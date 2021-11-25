<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TrackingCodeValidate extends AbstractMigration {
    public function up(): void {
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.invalid_starting_tracking_code()
    RETURNS character varying[]
    LANGUAGE 'sql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
SELECT '{"GK", "GC", "OP", "OK", "GE", "OZ", "OU", "ON", "OL", "OJ", "OS", "GD", "GA", "VI", "MS", "TR", "EX", "GR", "RH", "OX", "OB", "OR", "LT", "LV"}'::character varying[]
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.is_tracking_code_valid(IN tracking_code character varying)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF LENGTH(tracking_code) < 6 THEN
    RETURN FALSE;
ELSIF UPPER(SUBSTRING(tracking_code, 1, 2)) = ANY (invalid_starting_tracking_code()) THEN
    RETURN FALSE;
END IF;

RETURN TRUE;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.generate_tracking_code(IN size integer DEFAULT 6)
    RETURNS character varying
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
DECLARE
    tracking_code character varying := '';
BEGIN

WHILE NOT(is_tracking_code_valid(tracking_code)) LOOP
    SELECT array_to_string(array(select substr('ABCDEFGHJKMNPQRSTUVWXYZ23456789',((random()*(31-1)+1)::integer),1) from generate_series(1,size)),'')
        INTO tracking_code;
END LOOP;

RETURN tracking_code;
END;
$BODY$;
EOL;
        $this->execute($sql);

        // ## This is not possible to apply because of actual values in DB
        //        $sql = <<<'EOL'
        //ALTER TABLE IF EXISTS geokrety.gk_geokrety
        //    ADD CONSTRAINT tracking_code CHECK (is_tracking_code_valid(tracking_code))
        //    NOT VALID;
        //EOL;
        //        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.geokret_tracking_code()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF NEW.tracking_code IS NOT NULL THEN
    NEW.tracking_code = UPPER(NEW.tracking_code);
    IF is_tracking_code_valid(NEW.tracking_code) THEN
        RETURN NEW;
    END IF;
    RAISE 'Tracking code is invalid';
END IF;

NEW.tracking_code = generate_tracking_code();

RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        // ## This is not possible to apply because of actual values in DB
        //        $sql = <<<'EOL'
        //ALTER TABLE IF EXISTS geokrety.gk_geokrety
        //    DROP CONSTRAINT tracking_code;
        //EOL;
        //        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.generate_tracking_code(IN size integer DEFAULT 6)
    RETURNS character varying
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
<<mylabel>>
DECLARE
    tracking_code character varying := '';
BEGIN

WHILE NOT(is_tracking_code_valid(tracking_code)) OR (SELECT COUNT(*) > 0 FROM gk_geokrety AS gkg WHERE gkg.tracking_code = mylabel.tracking_code) LOOP
    SELECT array_to_string(array(select substr('ABCDEFGHJKMNPQRSTUVWXYZ23456789',((random()*(31-1)+1)::integer),1) from generate_series(1,size)),'')
        INTO tracking_code;
END LOOP;

RETURN tracking_code;
END;
$BODY$;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
DROP FUNCTION is_tracking_code_valid;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
DROP FUNCTION invalid_starting_tracking_code;
EOL;
        $this->execute($sql);

        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.geokret_tracking_code()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
DECLARE
found_tc bool;
BEGIN

IF (NEW.tracking_code IS NOT NULL AND LENGTH(NEW.tracking_code) >= 6) THEN
    NEW.tracking_code = UPPER(NEW.tracking_code);
    RETURN NEW;
END IF;

LOOP
    NEW.tracking_code = generate_tracking_code();
    SELECT COUNT(*) = 0 FROM gk_geokrety WHERE tracking_code = NEW.tracking_code INTO found_tc;
    EXIT WHEN found_tc;
END LOOP;

RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);
    }
}
