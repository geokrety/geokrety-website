<?php

use Phinx\Migration\AbstractMigration;

class LabelsLists extends AbstractMigration {
    public function change() {
        $table_labels = $this->table('gk_labels');
        $table_labels->addColumn('template', 'string', ['limit' => 128])
            ->addColumn('title', 'string', ['limit' => 512])
            ->addColumn('author', 'string', ['limit' => 128])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addIndex(['title'])
            ->addIndex(['template'])
            ->create();
        if ($this->isMigratingUp()) {
            $table_labels->insert([
                ['template' => 'default', 'title' => 'Default', 'author' => 'GK Team'],
                ['template' => 'sansanchoz1', 'title' => 'Key chain HR', 'author' => 'SanSanchoz'],
                ['template' => 'sansanchoz2', 'title' => 'Key chain VR', 'author' => 'SanSanchoz'],
                ['template' => 'schrottie', 'title' => 'Modern Schrottie', 'author' => 'Schrottie'],
                ['template' => 'stamp.black', 'title' => 'Stamp Black', 'author' => 'GK Team'],
                ['template' => 'stamp.blue', 'title' => 'Stamp Blue', 'author' => 'GK Team'],
                ['template' => 'stamp.green', 'title' => 'Stamp Green', 'author' => 'GK Team'],
                ['template' => 'stamp.orange', 'title' => 'Stamp Orange', 'author' => 'GK Team'],
                ['template' => 'stamp.purple', 'title' => 'Stamp Purple', 'author' => 'GK Team'],
                ['template' => 'stamp.red', 'title' => 'Stamp Red', 'author' => 'GK Team'],
                ['template' => 'stamp.white', 'title' => 'Stamp White', 'author' => 'GK Team'],
                ['template' => 'stamp.yellow', 'title' => 'Stamp Yellow', 'author' => 'GK Team'],
                ['template' => 'middleclassic', 'title' => 'Middle classic', 'author' => 'Filips'],
                ['template' => 'wallson1', 'title' => 'Modern Wallson 1', 'author' => 'Wallson'],
                ['template' => 'wallson2', 'title' => 'Modern Wallson 2', 'author' => 'Wallson'],
            ])->save();
        }

        $table_geokrety = $this->table('gk_geokrety');
        $table_geokrety->addColumn('label_template', 'integer', ['null' => true])
            ->addForeignKey('label_template', 'gk_labels', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->update();
    }
}
