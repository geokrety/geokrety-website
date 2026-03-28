<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewSansanchoz1smallLabelTemplate extends AbstractMigration {
    public function up(): void {
        $table = $this->table('gk_labels');
        $table->insert([
            [
                'template' => 'sansanchoz1small',
                'title' => 'Key chain HR - Smaller text',
                'author' => 'Detroit',
            ],
        ])->saveData();
    }

    public function down(): void {
        $this->execute("DELETE FROM gk_labels WHERE template = 'sansanchoz1small';");
    }
}
