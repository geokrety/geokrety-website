<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Collectible extends AbstractMigration {
    public function up(): void {
        $table_gk = $this->table('geokrety.gk_geokrety');
        $table_gk->addColumn('non_collectible', 'timestamp', ['default' => null, 'null' => true, 'timezone' => true])
            ->save();

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.moves_check_non_collectible()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
DECLARE
	gk gk_geokrety%ROWTYPE;
BEGIN

-- Load GeoKret
SELECT *
INTO gk
FROM gk_geokrety
WHERE id = NEW.geokret;

IF gk.non_collectible IS NULL THEN
	RETURN NEW;
END IF;

IF gk.non_collectible > NEW.moved_on_datetime THEN
	RETURN NEW;
END IF;

-- prevent dropped grabbed dipped form non-holder+non-owner
IF (gk.holder != NEW.author AND gk.owner != NEW.author) AND NEW.move_type IN (0::smallint, 1::smallint, 5::smallint) THEN
	RAISE 'Author(%) ; Holder(%) of non collectible(%) GeoKret(%) cannot do log of type DROP/GRAB/DIPPED (%)', NEW.author, gk.holder, gk.non_collectible, gk.id, NEW.move_type;
END IF;

-- prevent grabbed from holder
IF gk.holder = NEW.author AND gk.owner != NEW.author AND NEW.move_type = 1::smallint THEN
	RAISE 'Holder of non collectible GeoKret cannot do log of type GRAB';
END IF;

-- disable non_collectible flag on drop
IF 0::smallint = NEW.move_type THEN
	UPDATE "gk_geokrety"
	SET non_collectible=NULL
	WHERE id = NEW.geokret
	AND non_collectible IS NOT NULL;
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE FUNCTION geokrety.grokret_check_non_collectible()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
BEGIN

-- Set collectible require an holder
IF NEW.non_collectible IS NOT NULL AND NEW.holder IS NULL AND OLD.holder IS NULL THEN
	RAISE 'Cannot set non collectible without an holder';
END IF;

RETURN NEW;
END;
$BODY$;
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE TRIGGER before_60_check_non_collectible
    BEFORE INSERT OR UPDATE OF move_type
    ON geokrety.gk_moves
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.moves_check_non_collectible();
EOL);

        $this->execute(<<<'EOL'
CREATE OR REPLACE TRIGGER before_50_check_non_collectible
    BEFORE INSERT OR UPDATE
    ON geokrety.gk_geokrety
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.grokret_check_non_collectible();
EOL);
    }

    public function down(): void {
        $this->execute('DROP TRIGGER before_60_check_non_collectible ON geokrety.gk_moves');
        $this->execute('DROP TRIGGER before_50_check_non_collectible ON geokrety.gk_geokrety');

        $this->execute(<<<'EOL'
DROP FUNCTION IF EXISTS geokrety.moves_check_non_collectible();
DROP FUNCTION IF EXISTS geokrety.grokret_check_non_collectible();
EOL);

        $table_gk = $this->table('geokrety.gk_geokrety');
        $table_gk->removeColumn('non_collectible')
            ->save();
    }
}
