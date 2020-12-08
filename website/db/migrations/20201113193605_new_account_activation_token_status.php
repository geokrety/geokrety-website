<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewAccountActivationTokenStatus extends AbstractMigration {
    public function up() {
        $this->execute('ALTER TABLE geokrety.gk_account_activation DROP CONSTRAINT validate_used;');
        $this->execute('ALTER TABLE geokrety.gk_account_activation ADD CONSTRAINT validate_used CHECK (used = ANY (ARRAY[0, 1, 2, 3]));');
        $this->execute('
CREATE OR REPLACE FUNCTION geokrety.account_activation_check_validating_ip(IN validating_ip inet,IN used smallint)
    RETURNS boolean
    LANGUAGE \'plpgsql\'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF used = ANY (\'{0,2}\'::smallint[]) AND validating_ip IS NULL THEN
	RETURN TRUE;
ELSIF used = ANY (\'{1,3}\'::smallint[]) AND validating_ip IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;
$BODY$;
        ');
    }

    public function down() {
        $this->execute('ALTER TABLE geokrety.gk_account_activation DROP CONSTRAINT validate_used;');
        $this->execute('ALTER TABLE geokrety.gk_account_activation ADD CONSTRAINT validate_used CHECK (used = ANY (ARRAY[0, 1, 2]));');
        $this->execute('
CREATE OR REPLACE FUNCTION geokrety.account_activation_check_validating_ip(IN validating_ip inet,IN used smallint)
    RETURNS boolean
    LANGUAGE \'plpgsql\'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF used = ANY (\'{0,2}\'::smallint[]) AND validating_ip IS NULL THEN
	RETURN TRUE;
ELSIF used = 1::smallint AND validating_ip IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;
$BODY$;
        ');
    }
}
