<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGkMovesSourceColumns extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
ALTER TABLE geokrety.gk_moves
  ADD COLUMN IF NOT EXISTS previous_move_id BIGINT,
  ADD COLUMN IF NOT EXISTS previous_position_id BIGINT,
  ADD COLUMN IF NOT EXISTS km_distance NUMERIC(8,3);
SQL
        );

        $this->execute(<<<'SQL'
ALTER TABLE geokrety.gk_moves
  DROP CONSTRAINT IF EXISTS fk_gk_moves_previous_move;

ALTER TABLE geokrety.gk_moves
  ADD CONSTRAINT fk_gk_moves_previous_move
  FOREIGN KEY (previous_move_id) REFERENCES geokrety.gk_moves(id)
  DEFERRABLE INITIALLY DEFERRED;
SQL
        );

        $this->execute(<<<'SQL'
ALTER TABLE geokrety.gk_moves
  DROP CONSTRAINT IF EXISTS fk_gk_moves_previous_position;

ALTER TABLE geokrety.gk_moves
  ADD CONSTRAINT fk_gk_moves_previous_position
  FOREIGN KEY (previous_position_id) REFERENCES geokrety.gk_moves(id)
  DEFERRABLE INITIALLY DEFERRED;
SQL
        );

        $this->execute(<<<'SQL'
COMMENT ON COLUMN geokrety.gk_moves.previous_move_id IS 'FK to the most recent earlier qualifying move of the same GK; populated for both qualifying and non-qualifying rows so trail history keeps the last qualifying predecessor.';
COMMENT ON COLUMN geokrety.gk_moves.previous_position_id IS 'FK to the most recent earlier qualifying move of the same GK that has coordinates; populated even on non-position rows so trail history keeps the last positioned predecessor.';
COMMENT ON COLUMN geokrety.gk_moves.km_distance IS 'Great-circle distance in km from previous_position_id to this move position; NUMERIC(8,3) for deterministic aggregation.';
SQL
        );
    }

    public function down(): void {
        $this->execute('ALTER TABLE geokrety.gk_moves DROP CONSTRAINT IF EXISTS fk_gk_moves_previous_position;');
        $this->execute('ALTER TABLE geokrety.gk_moves DROP CONSTRAINT IF EXISTS fk_gk_moves_previous_move;');
        $this->execute('ALTER TABLE geokrety.gk_moves DROP COLUMN IF EXISTS km_distance;');
        $this->execute('ALTER TABLE geokrety.gk_moves DROP COLUMN IF EXISTS previous_position_id;');
        $this->execute('ALTER TABLE geokrety.gk_moves DROP COLUMN IF EXISTS previous_move_id;');
    }
}
