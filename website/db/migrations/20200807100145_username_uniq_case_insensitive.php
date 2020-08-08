<?php

use Phinx\Migration\AbstractMigration;

class UsernameUniqCaseInsensitive extends AbstractMigration {
    public function up() {
        $table = $this->table('gk_users');
        $table->removeIndexByName('gk_users_username_uniq')
            ->removeIndexByName('gk_users_username')
            ->update();
        $this->execute('CREATE UNIQUE INDEX gk_users_username_uniq ON gk_users (lower(username))');
        $this->execute('CREATE FUNCTION geokrety.user_trim_spaces()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
BEGIN

SELECT TRIM(regexp_replace(NEW.username, \'[\s\v\u0009\u0020\u00A0\u1680\u2000-\u200A\u202F\u205F\u3000]+\', \' \', \'g\'))
INTO NEW.username;

RETURN NEW;
END;
$BODY$;');
        $this->execute('CREATE TRIGGER before_30_username_trim_spaces
    BEFORE INSERT OR UPDATE OF username
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.user_trim_spaces();');
        $this->execute('CREATE FUNCTION geokrety.user_username_as_email_not_taken()
    RETURNS trigger
    LANGUAGE \'plpgsql\'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
DECLARE
	_username_hash bytea;
BEGIN

_username_hash = public.digest(NEW.username::character varying, \'sha256\');

IF (TG_OP = \'UPDATE\') THEN
	IF ((SELECT count(*) FROM gk_users WHERE _email_hash = _username_hash) > 0 AND OLD.id != NEW.id) THEN
		RAISE EXCEPTION \'duplicate key value violates unique constraint "gk_users_username_email_uniq"\' USING ERRCODE = \'unique_violation\';
	END IF;
	RETURN NEW;
END IF;

IF ((SELECT count(*) FROM gk_users WHERE _email_hash = _username_hash) > 0) THEN
	RAISE EXCEPTION \'duplicate key value violates unique constraint "gk_users_username_email_uniq"\' USING ERRCODE = \'unique_violation\';
END IF;

RETURN NEW;
END;
$BODY$;');
        $this->execute('CREATE TRIGGER before_40_username_email_uniq
    BEFORE INSERT OR UPDATE OF username
    ON geokrety.gk_users
    FOR EACH ROW
    EXECUTE PROCEDURE geokrety.user_username_as_email_not_taken();');
    }

    public function down() {
        $table = $this->table('gk_users');
        $table->removeIndexByName('gk_users_username_uniq')
            ->addIndex(['username'], ['unique' => true, 'name' => 'gk_users_username_uniq'])
            ->addIndex(['username'], ['unique' => true, 'name' => 'gk_users_username'])
            ->update();
        $this->execute('DROP TRIGGER before_30_username_trim_spaces ON geokrety.gk_users');
        $this->execute('DROP FUNCTION geokrety.user_trim_spaces()');
        $this->execute('DROP TRIGGER before_40_username_email_uniq ON geokrety.gk_users');
        $this->execute('DROP FUNCTION geokrety.user_username_as_email_not_taken()');
    }
}
