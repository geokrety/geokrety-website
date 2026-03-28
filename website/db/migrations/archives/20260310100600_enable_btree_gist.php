<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EnableBtreeGist extends AbstractMigration {
    public function up(): void {
        $this->execute('CREATE EXTENSION IF NOT EXISTS btree_gist;');
    }

    public function down(): void {
        $this->execute('DROP EXTENSION IF EXISTS btree_gist;');
    }
}
