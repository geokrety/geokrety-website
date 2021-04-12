<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DailyMails extends AbstractMigration {
    public function change(): void {
        $table = $this->table('gk_moves_comments');
        $table->addIndex('created_on_datetime', ['name' => 'idx_gk_moves_comments_created_on_datetime'])
            ->save();
    }
}
