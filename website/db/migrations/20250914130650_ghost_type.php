<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GhostType extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
ALTER TABLE IF EXISTS geokrety.gk_geokrety
    DROP CONSTRAINT validate_type;
ALTER TABLE IF EXISTS geokrety.gk_geokrety
    ADD CONSTRAINT validate_type CHECK (type = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]));
SQL
        );
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.geokret_manage_type()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF NEW.type IN (2, 6, 8) THEN
	IF NEW.holder != NEW.owner OR NEW.holder IS NULL THEN
		RAISE 'You must hold the Geokrety to change to this type';
	END IF;
	IF NEW.non_collectible IS NULL THEN
	    NEW.non_collectible := NOW();
	END IF;
ELSIF OLD.type IN (2, 6, 8) THEN
	NEW.non_collectible := NULL;
END IF;

IF NEW.type = 10 THEN
    -- Set both non_collectible and parked to current timestamp
    NEW.non_collectible := NOW();
    NEW.parked := NOW();
    -- Note: When returning from type 10 to another type, we do nothing
END IF;

RETURN NEW;
END;
$BODY$;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
ALTER TABLE IF EXISTS geokrety.gk_geokrety
    DROP CONSTRAINT validate_type;
ALTER TABLE IF EXISTS geokrety.gk_geokrety
    ADD CONSTRAINT validate_type CHECK (type = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6, 7, 8, 9]))
    NOT VALID;
SQL
        );
        $this->execute(<<<'SQL'
CREATE OR REPLACE FUNCTION geokrety.geokret_manage_type()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF NEW.type IN (2, 6, 8) THEN
	IF NEW.holder != NEW.owner OR NEW.holder IS NULL THEN
		RAISE 'You must hold the Geokrety to change to this type';
	END IF;
	IF NEW.non_collectible IS NULL THEN
	    NEW.non_collectible := NOW();
	END IF;
ELSIF OLD.type IN (2, 6, 8) THEN
	NEW.non_collectible := NULL;
END IF;

RETURN NEW;
END;
$BODY$;
SQL
        );
    }
}
