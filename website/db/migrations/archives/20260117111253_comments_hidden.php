<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CommentsHidden extends AbstractMigration {
    public function up(): void {
        $table_geokrety = $this->table('geokrety.gk_geokrety');
        $table_geokrety->addColumn('comments_hidden', 'boolean', [
            'default' => false,
            'null' => false,
            'after' => 'description',
        ])
            ->update();
        $table_moves = $this->table('geokrety.gk_moves');
        $table_moves->addColumn('comment_hidden', 'boolean', [
            'default' => false,
            'null' => false,
            'after' => 'comment',
        ])
            ->update();
        $table_users_settings_parameters = $this->table('geokrety.gk_users_settings_parameters');
        $table_users_settings_parameters->insert([
            [
                'name' => 'HIDDEN_COMMENTS_REVEAL_ALL',
                'type' => 'bool',
                'default' => 'false',
                'description' => 'Show every comment that was marked hidden',
            ],
            [
                'name' => 'HIDDEN_COMMENTS_REVEAL_OWNED_GEOKRETY',
                'type' => 'bool',
                'default' => 'true',
                'description' => 'Show hidden comments only for GeoKrety you own',
            ],
        ])->saveData();
    }

    public function down(): void {
        $this->table('geokrety.gk_geokrety')
            ->removeColumn('comments_hidden')
            ->update();
        $this->table('geokrety.gk_moves')
            ->removeColumn('comment_hidden')
            ->update();
        $this->execute(<<<EOL
            DELETE FROM geokrety.gk_users_settings_parameters
            WHERE name IN ('HIDDEN_COMMENTS_REVEAL_ALL', 'HIDDEN_COMMENTS_REVEAL_OWNED_GEOKRETY');
EOL);
    }
}
