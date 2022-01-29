<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class WatchedUniqIndex extends AbstractMigration {
    public function change(): void {
        $table = $this->table('gk_watched');
        $table->addIndex(['user', 'geokret'], ['unique' => true])
            ->save();
    }
}
