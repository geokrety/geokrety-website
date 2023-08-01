<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuditPostUserAgent extends AbstractMigration {
    public function change(): void {
        $this->table('audit.posts')
            ->addColumn('user_agent', 'text', ['null' => true])
            ->save();
    }
}
