<?php

use Phinx\Migration\AbstractMigration;

class SessionPersist extends AbstractMigration {
    public function change() {
        $table = $this->table('sessions');
        $table->addColumn('persistent', 'boolean', ['null' => false, 'default' => false])
            ->update();
    }
}
