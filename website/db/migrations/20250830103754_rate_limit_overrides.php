<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RateLimitOverrides extends AbstractMigration {
    public function up(): void {
        // create table
        $table = $this->table('geokrety.gk_rate_limit_overrides', [
            'id' => 'id',
            'primary_key' => 'id',
        ]);

        $table
            ->addColumn('user', 'integer', ['null' => false])
            ->addColumn('level', 'smallinteger', ['default' => 0, 'null' => false])

            // optional window
            ->addColumn('starts_at', 'timestamp', ['default' => null, 'null' => true, 'timezone' => true])
            ->addColumn('ends_at', 'timestamp', ['default' => null, 'null' => true, 'timezone' => true])

            // timestamps
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false, 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['default' => null, 'null' => true, 'timezone' => true])

            // indexes
            ->addIndex(['user'], ['name' => 'gk_rlovr_user_idx'])
            ->addIndex(['level'], ['name' => 'gk_rlovr_level_idx'])
            ->addIndex(['starts_at', 'ends_at'], ['name' => 'gk_rlovr_window_idx'])
            ->addIndex(['user', 'starts_at', 'ends_at'], ['name' => 'gk_rlovr_user_window_idx'])
            ->addForeignKey(
                'user',
                'geokrety.gk_users',
                'id',
                ['delete' => 'CASCADE', 'update' => 'NO_ACTION', 'constraint' => 'gk_rlovr_user_fkey']
            )
            ->create();

        // enforce non-negative levels (Postgres)
        $this->execute('ALTER TABLE geokrety.gk_rate_limit_overrides ADD CONSTRAINT gk_rlovr_level_nonneg CHECK (level >= 0)');

        // trigger to auto-update updated_on_datetime (uses existing function geokrety.on_update_current_timestamp())
        $this->execute(<<<'SQL'
CREATE OR REPLACE TRIGGER before_00_updated_on_datetime
    BEFORE UPDATE ON geokrety.gk_rate_limit_overrides
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.on_update_current_timestamp();
SQL
        );
    }

    public function down(): void {
        // drop trigger first
        $this->execute('DROP TRIGGER IF EXISTS before_00_updated_on_datetime ON geokrety.gk_rate_limit_overrides');

        // drop FK then table
        $this->table('geokrety.gk_rate_limit_overrides')
            ->dropForeignKey('user', 'gk_rlovr_user_fkey')
            ->save();

        $this->table('geokrety.gk_rate_limit_overrides')->drop()->save();
    }
}
