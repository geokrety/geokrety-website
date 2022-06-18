<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuditLogSessionID extends AbstractMigration {
    public function change(): void {
        $actions_logs = $this->table('audit.actions_logs');
        $actions_logs
            ->addColumn('session', 'string', ['null' => false, 'default' => ''])
            ->save();
        $posts = $this->table('audit.posts');
        $posts
            ->addColumn('session', 'string', ['null' => false, 'default' => ''])
            ->save();
    }
}
