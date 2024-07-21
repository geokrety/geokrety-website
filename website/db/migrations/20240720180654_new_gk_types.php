<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewGkTypes extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
ALTER TABLE IF EXISTS geokrety.gk_geokrety
DROP CONSTRAINT validate_type;

ALTER TABLE IF EXISTS geokrety.gk_geokrety
ADD CONSTRAINT validate_type CHECK (type = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6, 7, 8, 9]));
EOL);

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.geokret_manage_type()
    RETURNS trigger
    LANGUAGE 'plpgsql'
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
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE TRIGGER before_45_manage_type
BEFORE INSERT OR UPDATE OF type, non_collectible
ON geokrety.gk_geokrety
FOR EACH ROW
EXECUTE FUNCTION geokrety.geokret_manage_type();
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.map_geokrety_types(IN type smallint)
    RETURNS character varying
    LANGUAGE 'plpgsql'
    VOLATILE
    PARALLEL UNSAFE
    COST 100

AS $BODY$
BEGIN

IF type = 0 THEN
	RETURN 'Traditional';
ELSIF type = 1 THEN
	RETURN 'Book/CD/DVD…';
ELSIF type = 2 THEN
	RETURN 'Human/Pet';
ELSIF type = 3 THEN
	RETURN 'Coin';
ELSIF type = 4 THEN
	RETURN 'KretyPost';
ELSIF type = 5 THEN
	RETURN 'Pebble';
ELSIF type = 6 THEN
	RETURN 'Car';
ELSIF type = 7 THEN
	RETURN 'Playing card';
ELSIF type = 8 THEN
	RETURN 'Dog tag/pet';
ELSIF type = 9 THEN
	RETURN 'Jigsaw part';
END IF;

RAISE 'Unknown GeoKrety type';
END;
$BODY$;
EOL);
    }

    public function down(): void {
        $this->execute(<<<'EOL'
ALTER TABLE IF EXISTS geokrety.gk_geokrety
DROP CONSTRAINT validate_type;

ALTER TABLE IF EXISTS geokrety.gk_geokrety
ADD CONSTRAINT validate_type CHECK (type = ANY (ARRAY[0, 1, 2, 3, 4]));
EOL);
        $this->execute('DROP TRIGGER IF EXISTS before_45_manage_type ON geokrety.gk_geokrety');

        $this->execute(<<<'EOL'
DROP FUNCTION IF EXISTS geokrety.geokret_manage_type();
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.map_geokrety_types(IN type smallint)
RETURNS character varying
LANGUAGE 'plpgsql'
VOLATILE
PARALLEL UNSAFE
COST 100

AS $BODY$
BEGIN

IF type = 0 THEN
RETURN 'Traditional';
ELSIF type = 1 THEN
RETURN 'Book/CD/DVD…';
ELSIF type = 2 THEN
RETURN 'Human/Pet';
ELSIF type = 3 THEN
RETURN 'Coin';
ELSIF type = 4 THEN
RETURN 'KretyPost';
END IF;

RAISE 'Unknown GeoKrety type';
END;
$BODY$;
EOL);
    }
}
