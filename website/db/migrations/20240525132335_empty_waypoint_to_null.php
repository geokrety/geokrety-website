<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmptyWaypointToNull extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.moves_waypoint_uppercase()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN
    IF NEW.waypoint = '' THEN
        NEW.waypoint = NULL;
    END IF;

    NEW.waypoint = UPPER(NEW.waypoint);
    RETURN NEW;
END;
$BODY$;
EOL);
    }

    public function down(): void {
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.moves_waypoint_uppercase()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN
    NEW.waypoint = UPPER(NEW.waypoint);
    RETURN NEW;
END;
$BODY$;
EOL);
    }
}
