<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserIdInSession extends AbstractMigration {
    public function change(): void {
        $table = $this->table('sessions');
        $table->addColumn('user', 'biginteger', ['null' => true, 'default' => null])
            ->save();
    }
}
