<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StatusMapper extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE FUNCTION geokrety.map_account_status(IN status smallint)
    RETURNS character varying
    LANGUAGE 'plpgsql'

AS $BODY$
BEGIN

IF status = 0 THEN
	RETURN 'Non activated';
ELSIF status = 1 THEN
	RETURN 'Active';
ELSIF status = 2 THEN
	RETURN 'Imported';
END IF;

RAISE 'Unknown account status';
END;
$BODY$;

ALTER FUNCTION geokrety.map_account_status(smallint);
SQL);
        $this->execute(<<<'SQL'
CREATE FUNCTION geokrety.map_geokrety_types(IN type smallint)
    RETURNS character varying
    LANGUAGE 'plpgsql'

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

ALTER FUNCTION geokrety.map_geokrety_types(smallint);
SQL);
        $this->execute(<<<'SQL'
CREATE FUNCTION geokrety.map_move_types(IN type smallint)
    RETURNS character varying
    LANGUAGE 'plpgsql'

AS $BODY$
BEGIN

IF type = 0 THEN
	RETURN 'Dropped';
ELSIF type = 1 THEN
	RETURN 'Grabbed';
ELSIF type = 2 THEN
	RETURN 'Comment';
ELSIF type = 3 THEN
	RETURN 'Seen';
ELSIF type = 4 THEN
	RETURN 'Archived';
ELSIF type = 5 THEN
	RETURN 'Visiting';
END IF;

RAISE 'Unknown Move type';
END;
$BODY$;

ALTER FUNCTION geokrety.map_move_types(smallint);
SQL);
        $this->execute(<<<'SQL'
CREATE FUNCTION geokrety.map_pictures_types(IN type smallint)
    RETURNS character varying
    LANGUAGE 'plpgsql'

AS $BODY$
BEGIN

IF type = 0 THEN
	RETURN 'GK Avatar';
ELSIF type = 1 THEN
	RETURN 'GK Move';
ELSIF type = 2 THEN
	RETURN 'User Avatar';
END IF;

RAISE 'Unknown Picture type';
END;
$BODY$;

ALTER FUNCTION geokrety.map_pictures_types(smallint);
SQL);
        $this->execute(<<<'SQL'
CREATE FUNCTION geokrety.map_move_comments_types(IN type smallint)
    RETURNS character varying
    LANGUAGE 'plpgsql'

AS $BODY$
BEGIN

IF type = 0 THEN
	RETURN 'Comment';
ELSIF type = 1 THEN
	RETURN 'Missing';
END IF;

RAISE 'Unknown Move Comment type';
END;
$BODY$;

ALTER FUNCTION geokrety.map_move_comments_types(smallint);
SQL);
    }

    public function down(): void {
        $this->execute('DROP FUNCTION map_account_status');
        $this->execute('DROP FUNCTION map_geokrety_types');
        $this->execute('DROP FUNCTION map_move_types');
        $this->execute('DROP FUNCTION map_pictures_types');
        $this->execute('DROP FUNCTION map_move_comments_types');
    }
}
