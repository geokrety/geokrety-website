<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EnsureUserListingLastMoveIndex extends AbstractMigration {
    public function up(): void {
        $this->execute('COMMIT;');
        $this->execute(
            <<<'SQL'
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gk_moves_author_last_move_lookup_v2
ON geokrety.gk_moves (author, moved_on_datetime DESC)
WHERE author IS NOT NULL;
SQL,
        );
        $this->execute('BEGIN;');
    }

    public function down(): void {
        $this->execute('COMMIT;');
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS geokrety.idx_gk_moves_author_last_move_lookup_v2;');
        $this->execute('BEGIN;');
    }
}
