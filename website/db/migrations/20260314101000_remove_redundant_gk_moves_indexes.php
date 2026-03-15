<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveRedundantGkMovesIndexes extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS(
        SELECT 1 FROM pg_indexes
        WHERE schemaname = 'geokrety'
          AND tablename = 'gk_moves'
          AND indexname = 'idx_gk_moves_prev_loc_lookup'
    ) THEN
        RAISE EXCEPTION 'Critical index idx_gk_moves_prev_loc_lookup not found; aborting cleanup.';
    END IF;

    IF NOT EXISTS(
        SELECT 1 FROM pg_indexes
        WHERE schemaname = 'geokrety'
          AND tablename = 'gk_moves'
          AND indexname = 'idx_gk_moves_qualified_period'
    ) THEN
        RAISE EXCEPTION 'Critical index idx_gk_moves_qualified_period not found; aborting cleanup.';
    END IF;

    IF NOT EXISTS(
        SELECT 1 FROM pg_indexes
        WHERE schemaname = 'geokrety'
          AND tablename = 'gk_moves'
          AND indexname = 'idx_gk_moves_geokret_chainlookup'
    ) THEN
        RAISE EXCEPTION 'Critical index idx_gk_moves_geokret_chainlookup not found; aborting cleanup.';
    END IF;
END $$;
SQL
        );

        $this->execute(<<<'SQL'
DO $$
BEGIN
    IF EXISTS(
        SELECT 1 FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE n.nspname = 'geokrety'
          AND c.relname = 'idx_21044_primary'
    ) AND NOT EXISTS(
        SELECT 1 FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE n.nspname = 'geokrety'
          AND c.relname = 'gk_moves_pkey'
    ) THEN
        ALTER INDEX geokrety.idx_21044_primary RENAME TO gk_moves_pkey;
    END IF;
END $$;
SQL
        );

        $this->execute(<<<'SQL'
DROP INDEX IF EXISTS geokrety.idx_21044_lat;
DROP INDEX IF EXISTS geokrety.idx_21044_lon;
DROP INDEX IF EXISTS geokrety.idx_21044_data_dodania;
DROP INDEX IF EXISTS geokrety.idx_21044_timestamp;
DROP INDEX IF EXISTS geokrety.idx_21044_alt;
DROP INDEX IF EXISTS geokrety.idx_21044_data;
DROP INDEX IF EXISTS geokrety.idx_21044_waypoint;
DROP INDEX IF EXISTS geokrety.idx_21044_user;
DROP INDEX IF EXISTS geokrety.gk_moves_country_index;
DROP INDEX IF EXISTS geokrety.gk_moves_type_index;
DROP INDEX IF EXISTS geokrety.idx_moves_geokret;
DROP INDEX IF EXISTS geokrety.idx_moves_id;
DROP INDEX IF EXISTS geokrety.gk_moves_move_type_id;
DROP INDEX IF EXISTS geokrety.idx_moves_type_id;
DROP INDEX IF EXISTS geokrety.gk_moves_move_type_id_position;
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
CREATE INDEX IF NOT EXISTS idx_21044_lat ON geokrety.gk_moves USING btree (lat);
CREATE INDEX IF NOT EXISTS idx_21044_lon ON geokrety.gk_moves USING btree (lon);
CREATE INDEX IF NOT EXISTS idx_21044_data_dodania ON geokrety.gk_moves USING btree (moved_on_datetime);
CREATE INDEX IF NOT EXISTS idx_21044_timestamp ON geokrety.gk_moves USING btree (updated_on_datetime);
CREATE INDEX IF NOT EXISTS idx_21044_alt ON geokrety.gk_moves USING btree (elevation);
CREATE INDEX IF NOT EXISTS idx_21044_data ON geokrety.gk_moves USING btree (created_on_datetime);
CREATE INDEX IF NOT EXISTS idx_21044_waypoint ON geokrety.gk_moves USING btree (waypoint);
CREATE INDEX IF NOT EXISTS idx_21044_user ON geokrety.gk_moves USING btree (author);
CREATE INDEX IF NOT EXISTS gk_moves_country_index ON geokrety.gk_moves USING btree (country);
CREATE INDEX IF NOT EXISTS gk_moves_type_index ON geokrety.gk_moves USING btree (move_type);
CREATE INDEX IF NOT EXISTS idx_moves_geokret ON geokrety.gk_moves USING btree (geokret);
CREATE INDEX IF NOT EXISTS idx_moves_id ON geokrety.gk_moves USING btree (id);
CREATE INDEX IF NOT EXISTS gk_moves_move_type_id ON geokrety.gk_moves USING btree (move_type, id);
CREATE INDEX IF NOT EXISTS idx_moves_type_id ON geokrety.gk_moves USING btree (move_type, id);
CREATE INDEX IF NOT EXISTS gk_moves_move_type_id_position ON geokrety.gk_moves USING btree (move_type, id, "position");
SQL
        );

        $this->execute(<<<'SQL'
DO $$
BEGIN
    IF EXISTS(
        SELECT 1 FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE n.nspname = 'geokrety'
          AND c.relname = 'gk_moves_pkey'
    ) AND NOT EXISTS(
        SELECT 1 FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE n.nspname = 'geokrety'
          AND c.relname = 'idx_21044_primary'
    ) THEN
        ALTER INDEX geokrety.gk_moves_pkey RENAME TO idx_21044_primary;
    END IF;
END $$;
SQL
        );
    }
}
