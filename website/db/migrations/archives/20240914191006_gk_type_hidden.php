<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GkTypeHidden extends AbstractMigration {
    public function up(): void {
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
ELSIF type = 10 THEN
	RETURN 'Hidden GeoKret';
END IF;

RAISE 'Unknown GeoKrety type';
END;
$BODY$;
EOL);
    }

    public function down(): void {
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
}
