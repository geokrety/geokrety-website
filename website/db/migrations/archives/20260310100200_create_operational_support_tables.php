<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOperationalSupportTables extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.backfill_progress (
  job_name VARCHAR(100) PRIMARY KEY,
  target_table VARCHAR(100) NOT NULL,
  min_id BIGINT NOT NULL DEFAULT 0,
  max_id BIGINT NOT NULL,
  cursor_id BIGINT NOT NULL DEFAULT 0,
  batch_size INT NOT NULL DEFAULT 10000,
  status VARCHAR(20) NOT NULL DEFAULT 'pending'
    CHECK (status IN ('pending','running','paused','completed','failed')),
  rows_processed BIGINT NOT NULL DEFAULT 0,
  error_count INT NOT NULL DEFAULT 0,
  started_at TIMESTAMPTZ,
  last_heartbeat_at TIMESTAMPTZ,
  completed_at TIMESTAMPTZ,
  notes TEXT,
  last_error TEXT
);

COMMENT ON TABLE stats.backfill_progress IS 'Tracks resumable heavy batch operations with cursor checkpoints and status';
COMMENT ON COLUMN stats.backfill_progress.cursor_id IS 'Last successfully processed source row ID; resume from cursor_id + 1';
COMMENT ON COLUMN stats.backfill_progress.last_heartbeat_at IS 'Updated periodically during execution for liveness monitoring';

CREATE TABLE stats.job_log (
  id BIGSERIAL PRIMARY KEY,
  job_name VARCHAR(100) NOT NULL,
  status VARCHAR(20) NOT NULL,
  metadata JSONB,
  started_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  completed_at TIMESTAMPTZ
);

COMMENT ON TABLE stats.job_log IS 'Audit log for all backfill, replay, and snapshot operations';
COMMENT ON COLUMN stats.job_log.metadata IS 'Arbitrary JSON metadata: batch counts, timing, error details';
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP TABLE IF EXISTS stats.job_log;');
        $this->execute('DROP TABLE IF EXISTS stats.backfill_progress;');
    }
}
