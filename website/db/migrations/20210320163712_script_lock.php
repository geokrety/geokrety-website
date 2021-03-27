<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ScriptLock extends AbstractMigration {
    public function change(): void {
        $table = $this->table('scripts');
        $table->addColumn('locked_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->save();
    }
}
