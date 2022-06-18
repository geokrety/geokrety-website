<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuditErrors extends AbstractMigration {
    public function change(): void {
        $table = $this->table('audit.posts');
        $table
            ->addColumn('errors', 'json', ['null' => true])
            ->save();
    }
}
