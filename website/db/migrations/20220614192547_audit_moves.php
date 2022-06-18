<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuditMoves extends AbstractMigration {
    public function change(): void {
        $table = $this->table('audit.posts');
        $table
            ->addColumn('author', 'biginteger', ['null' => true])
            ->addColumn('ip', 'inet', ['null' => false])
            ->addColumn('route', 'string', ['null' => false, 'limit' => 256])
            ->addColumn('payload', 'json', ['null' => false])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addIndex(['created_on_datetime'])
            ->addIndex(['author'])
            ->addIndex(['ip'])
            ->addIndex(['route'])
            ->create();
    }
}
