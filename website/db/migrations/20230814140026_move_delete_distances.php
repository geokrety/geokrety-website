<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MoveDeleteDistances extends AbstractMigration {
    public function up(): void {
        $this->execute('ALTER TRIGGER after_20_distances ON geokrety.gk_moves RENAME TO after_30_distances;');
        $this->execute('ALTER TRIGGER after_30_last_log_and_position ON geokrety.gk_moves RENAME TO after_20_last_log_and_position;');
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.moves_distances_after()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
	IF (OLD.geokret != NEW.geokret) THEN
    -- Updating old position
		PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
		PERFORM geokret_compute_total_places_visited(OLD.geokret);
		PERFORM geokret_compute_total_distance(OLD.geokret);
	END IF;
	PERFORM update_next_move_distance(NEW.geokret, NEW.id);
END IF;

IF (TG_OP = 'DELETE') THEN
	PERFORM geokret_compute_total_distance(OLD.geokret);
	PERFORM geokret_compute_total_places_visited(OLD.geokret);
	RETURN OLD;
END IF;

PERFORM geokret_compute_total_distance(NEW.geokret);
PERFORM geokret_compute_total_places_visited(NEW.geokret);

RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $this->execute('ALTER TRIGGER after_20_last_log_and_position ON geokrety.gk_moves RENAME TO after_30_last_log_and_position;');
        $this->execute('ALTER TRIGGER after_30_distances ON geokrety.gk_moves RENAME TO after_20_distances;');
        $sql = <<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.moves_distances_after()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN

IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
	IF (OLD.geokret != NEW.geokret) THEN
    -- Updating old position
		PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
		PERFORM geokret_compute_total_places_visited(OLD.geokret);
		PERFORM geokret_compute_total_distance(OLD.geokret);
	END IF;
	PERFORM update_next_move_distance(NEW.geokret, NEW.id);
END IF;

-- IF (TG_OP = 'DELETE') THEN
-- 	PERFORM geokret_compute_total_distance(OLD.geokret);
-- 	PERFORM geokret_compute_total_places_visited(OLD.geokret);
-- 	RETURN OLD;
-- END IF;

PERFORM geokret_compute_total_distance(NEW.geokret);
PERFORM geokret_compute_total_places_visited(NEW.geokret);

RETURN NEW;
END;
$BODY$;
EOL;
        $this->execute($sql);
    }
}
