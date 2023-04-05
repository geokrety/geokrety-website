<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class EmailRevalidate extends AbstractMigration {
    public function change() {
        $table = $this->table('gk_email_revalidate', ['id' => false]);
        $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('user', 'biginteger', ['null' => false])
            ->addColumn('used', Literal::from('smallint'), ['null' => false, 'default' => 0, 'comment' => "TOKEN_UNUSED = 0\nTOKEN_VALIDATED = 1\nTOKEN_EXPIRED = 2\nTOKEN_DISABLED = 3"])
            ->addColumn('_email', Literal::from('character varying(128)'), ['null' => true])
            ->addColumn('token', Literal::from('character varying(60)'), ['null' => true, 'default' => null])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addColumn('validated_on_datetime', 'timestamp', ['null' => true, 'timezone' => true])
            ->addColumn('expired_on_datetime', 'timestamp', ['null' => true, 'timezone' => true])
            ->addColumn('disabled_on_datetime', 'timestamp', ['null' => true, 'timezone' => true])
            ->addColumn('validating_ip', 'inet', ['null' => true, 'default' => null])
            ->addColumn('_email_crypt', Literal::from('bytea'), ['null' => false])
            ->addColumn('_email_hash', Literal::from('bytea'), ['null' => false])
            ->addForeignKey('user', 'gk_users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex('token', ['unique' => true, 'name' => 'idx_token_unique'])
            ->save();
        if ($this->isMigratingUp()) {
            $this->execute('ALTER TABLE "gk_email_revalidate" ADD CONSTRAINT "idx_id_primary" PRIMARY KEY ("id");');
            $this->execute('
CREATE TRIGGER before_10_manage_tokens
BEFORE INSERT OR UPDATE OF token
ON geokrety.gk_email_revalidate
FOR EACH ROW
EXECUTE PROCEDURE geokrety.account_activation_token_generate();
');
            $this->execute('
CREATE TRIGGER before_20_manage_email
BEFORE INSERT OR UPDATE OF _email, _email_hash, _email_crypt
ON geokrety.gk_email_revalidate
FOR EACH ROW
EXECUTE PROCEDURE geokrety.manage_email();
');
            $this->execute('
CREATE FUNCTION geokrety.valid_email_revalidate_used()
    RETURNS smallint[]
    LANGUAGE \'sql\'

AS $BODY$
SELECT \'{0,1,2,3}\'::smallint[]
$BODY$;

ALTER FUNCTION geokrety.valid_email_revalidate_used();
');
            $this->execute('
ALTER TABLE geokrety.gk_email_revalidate
    ADD CONSTRAINT validate_used CHECK (used = ANY (valid_email_revalidate_used()));
');
            $this->execute('
CREATE FUNCTION geokrety.email_revalidate_check_only_one_active_per_user()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
     NOT LEAKPROOF
AS $BODY$
BEGIN

UPDATE "gk_email_revalidate"
SET used = 3 -- TOKEN_DISABLED
WHERE "user" = NEW.user
AND used = 0
AND id != NEW.id;

RETURN NEW;
END;
$BODY$;

ALTER FUNCTION geokrety.email_revalidate_check_only_one_active_per_user();
');
            $this->execute('
CREATE TRIGGER before_40_only_one_at_a_time
    BEFORE INSERT
    ON geokrety.gk_email_revalidate
    FOR EACH ROW
    WHEN (NEW.used = 0)
    EXECUTE PROCEDURE geokrety.email_revalidate_check_only_one_active_per_user();
');
            $this->execute('
CREATE TRIGGER after_10_only_one_at_a_time
    AFTER UPDATE
    ON geokrety.gk_email_revalidate
    FOR EACH ROW
    WHEN (NEW.used = 0)
    EXECUTE PROCEDURE geokrety.email_revalidate_check_only_one_active_per_user();
');
            $this->execute('
CREATE OR REPLACE FUNCTION geokrety.email_revalidate_validated_on_datetime_updater()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF NEW.used = 0::smallint THEN
	NEW.validating_ip = NULL;
	NEW.validated_on_datetime = NULL;
	NEW.expired_on_datetime = NULL;
	NEW.disabled_on_datetime = NULL;
ELSIF NEW.used = 1::smallint AND NEW.validated_on_datetime IS NULL THEN
	NEW.validated_on_datetime = NOW();
	NEW.expired_on_datetime = NULL;
	NEW.disabled_on_datetime = NULL;
ELSIF NEW.used = 2::smallint THEN
	NEW.validating_ip = NULL;
	NEW.validated_on_datetime = NULL;
	NEW.expired_on_datetime = NOW();
	NEW.disabled_on_datetime = NULL;
ELSIF NEW.used = 3::smallint THEN
	NEW.validating_ip = NULL;
	NEW.validated_on_datetime = NULL;
	NEW.expired_on_datetime = NULL;
	NEW.disabled_on_datetime = NOW();
END IF;

RETURN NEW;
END;
$BODY$;
');
            $this->execute('
CREATE TRIGGER before_30_manage_ip_validated_on_datetime
    BEFORE INSERT OR UPDATE OF used
    ON geokrety.gk_email_revalidate
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.email_revalidate_validated_on_datetime_updater();
');
            $this->execute('
ALTER TABLE geokrety.gk_email_revalidate
    ADD CONSTRAINT validated_ip CHECK (
        validating_ip IS NOT NULL AND used = 1 OR
        validating_ip IS NULL AND used != 1
    );
');
            $this->execute('
CREATE TRIGGER updated_on_datetime
    BEFORE UPDATE
    ON geokrety.gk_email_revalidate
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.on_update_current_timestamp();
');
            $this->execute('DROP INDEX idx_20969_user;');
            $this->execute('
CREATE UNIQUE INDEX idx_20969_user
    ON geokrety.gk_account_activation USING btree
    ("user" ASC NULLS LAST);
');
            $this->execute('ALTER TABLE gk_account_activation DROP CONSTRAINT validate_used;');
            $this->execute('
ALTER TABLE gk_account_activation
    ADD CONSTRAINT validate_used CHECK (used = ANY (ARRAY[0, 1]));
');
            $this->execute('
CREATE FUNCTION geokrety.email_revalidate_validated_update_user()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
     NOT LEAKPROOF
AS $BODY$
BEGIN

IF NEW.used = 1::smallint THEN
	UPDATE gk_users
	SET account_valid = 1
	WHERE id = NEW."user";
END IF;

RETURN NEW;
END;
$BODY$;
');
            $this->execute('
CREATE TRIGGER before_50_update_user_account_status
    BEFORE UPDATE OF used
    ON geokrety.gk_email_revalidate
    FOR EACH ROW
    WHEN (NEW.used = 1)
    EXECUTE PROCEDURE geokrety.email_revalidate_validated_update_user();
');
        } else {
            // Migrate Down
            $this->execute('DROP INDEX idx_20969_user;');
            $this->execute('
CREATE INDEX idx_20969_user
    ON geokrety.gk_account_activation USING btree
    ("user" ASC NULLS LAST);
');
            $this->execute('ALTER TABLE gk_account_activation DROP CONSTRAINT validate_used;');
            $this->execute('
ALTER TABLE gk_account_activation
    ADD CONSTRAINT validate_used CHECK (used = ANY (ARRAY[0, 1, 2, 3]));
');

            $this->execute('TRUNCATE gk_email_revalidate;');
            $this->execute('DROP TRIGGER updated_on_datetime ON gk_email_revalidate;');
            $this->execute('DROP TRIGGER before_10_manage_tokens ON gk_email_revalidate;');
            $this->execute('DROP TRIGGER before_20_manage_email ON gk_email_revalidate;');
            $this->execute('DROP TRIGGER before_30_manage_ip_validated_on_datetime ON gk_email_revalidate;');
            $this->execute('DROP TRIGGER before_40_only_one_at_a_time ON gk_email_revalidate;');
            $this->execute('DROP TRIGGER before_50_update_user_account_status ON gk_email_revalidate;');
            $this->execute('DROP TRIGGER after_10_only_one_at_a_time ON gk_email_revalidate;');
            $this->execute('ALTER TABLE gk_email_revalidate DROP CONSTRAINT validate_used;');
            $this->execute('DROP FUNCTION valid_email_revalidate_used;');
            $this->execute('DROP FUNCTION email_revalidate_check_only_one_active_per_user;');
            $this->execute('DROP FUNCTION email_revalidate_validated_update_user;');
        }
    }
}
