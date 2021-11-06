<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ScriptsLocks extends AbstractMigration {
    public function change(): void {
        $table_scripts = $this->table('scripts');
        $table_scripts->renameColumn('locked_datetime', 'locked_on_datetime')
            ->save();
    }
}
