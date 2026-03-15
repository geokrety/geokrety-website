<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateGeokretMoveHistoryView extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE OR REPLACE VIEW geokrety.vw_geokret_move_history AS
SELECT
  m.geokret,
  m.id AS move_id,
  m.previous_move_id,
  m.previous_position_id,
  CASE WHEN m.position IS NULL THEN '-' ELSE (ROUND(public.ST_Y(m.position::public.geometry)::numeric, 5)::text || ' ' || ROUND(public.ST_X(m.position::public.geometry)::numeric, 5)::text) END AS position,
  m.km_distance,
  m.move_type,
  CASE m.move_type
    WHEN 0 THEN 'drop'
    WHEN 1 THEN 'grab'
    WHEN 2 THEN 'comment'
    WHEN 3 THEN 'met'
    WHEN 4 THEN 'archive'
    WHEN 5 THEN 'dip'
    WHEN 9 THEN 'Born'
    ELSE 'unknown'
  END AS move_type_label,
  m.moved_on_datetime
FROM geokrety.gk_moves m
ORDER BY m.moved_on_datetime ASC, m.id ASC;

COMMENT ON VIEW geokrety.vw_geokret_move_history IS 'Denormalized move history view for Geokret trail logs; includes human-readable move_type_label and text position.';
SQL
        );
    }

    public function down(): void {
        $this->execute(<<<'SQL'
DROP VIEW IF EXISTS geokrety.vw_geokret_move_history;
SQL
        );
    }
}
