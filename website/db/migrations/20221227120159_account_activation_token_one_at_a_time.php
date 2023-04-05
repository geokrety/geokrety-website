<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AccountActivationTokenOneAtATime extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.account_activation_check_only_one_active_per_user()
    RETURNS trigger
    LANGUAGE 'plpgsql'
     NOT LEAKPROOF
AS $BODY$
BEGIN

IF NEW.used = 0::smallint THEN
    UPDATE "gk_account_activation"
    SET used = 3, -- TOKEN_DISABLED
    validating_ip = NULL,
    used_on_datetime = NULL
    WHERE "user" = NEW.user
    AND used = 0
    AND id != NEW.id;
END IF;

IF NEW.used = ANY ('{0,2,3}'::smallint[]) THEN
    NEW.used_on_datetime = NULL;
    NEW.validating_ip = NULL;
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE TRIGGER before_10_only_one_at_a_time
    BEFORE INSERT OR UPDATE OF used
    ON geokrety.gk_account_activation
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.account_activation_check_only_one_active_per_user();
EOL);

        $this->execute(<<<'EOL'
DROP INDEX idx_20969_user;
EOL);
        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.account_activation_disable_all()
    RETURNS trigger
    LANGUAGE 'plpgsql'
     NOT LEAKPROOF
AS $BODY$
BEGIN

UPDATE "gk_account_activation"
SET used = 3 -- TOKEN_DISABLED
WHERE "user" = NEW.id
AND used = 0;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE TRIGGER after_10_disable_all_account_activation
    BEFORE INSERT OR UPDATE OF account_valid
    ON geokrety.gk_users
    FOR EACH ROW
    WHEN (NEW.account_valid = 1)
    EXECUTE FUNCTION geokrety.account_activation_disable_all();
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.account_activation_check_validating_ip(IN validating_ip inet,IN used smallint)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF used = ANY ('{0,2}'::smallint[]) AND validating_ip IS NULL THEN
	RETURN TRUE;
ELSIF used = ANY ('{1}'::smallint[]) AND validating_ip IS NOT NULL THEN
	RETURN TRUE;
ELSIF used = ANY ('{3}'::smallint[]) THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
ALTER TABLE IF EXISTS geokrety.gk_account_activation
    DROP CONSTRAINT validate_used;
ALTER TABLE IF EXISTS geokrety.gk_account_activation
    ADD CONSTRAINT validate_used CHECK (used = ANY (ARRAY[0, 1, 2, 3]));
EOL);

        $this->execute(<<<'EOL'
COMMENT ON COLUMN geokrety.gk_account_activation.used
    IS '0=unused 1=validated 2=expired 3=disabled';
EOL);
    }

    public function down(): void {
        $this->execute('DROP TRIGGER "before_10_only_one_at_a_time" ON geokrety."gk_account_activation";');
        $this->execute('DROP FUNCTION geokrety.account_activation_check_only_one_active_per_user;');
        $this->execute(<<<EOL
CREATE UNIQUE INDEX idx_20969_user
    ON geokrety.gk_account_activation USING btree
    ("user" ASC NULLS LAST);
EOL);
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.account_activation_check_validating_ip(IN validating_ip inet,IN used smallint)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF used = ANY ('{0,2}'::smallint[]) AND validating_ip IS NULL THEN
	RETURN TRUE;
ELSIF used = ANY ('{1,3}'::smallint[]) AND validating_ip IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;
$BODY$;
EOL);
        $this->execute(<<<'EOL'
ALTER TABLE IF EXISTS geokrety.gk_account_activation
    DROP CONSTRAINT validate_used;
ALTER TABLE IF EXISTS geokrety.gk_account_activation
    ADD CONSTRAINT validate_used CHECK (used = ANY (ARRAY[0, 1]))
    NOT VALID;
EOL);
        $this->execute('DROP TRIGGER "after_10_disable_all_account_activation" ON geokrety."gk_users";');
        $this->execute('DROP FUNCTION geokrety.account_activation_disable_all;');
    }
}
