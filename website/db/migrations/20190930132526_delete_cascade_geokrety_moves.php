<?php

use Phinx\Db\Adapter\MysqlAdapter;

class DeleteCascadeGeokretyMoves extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("SET UNIQUE_CHECKS = 0;");
        $this->execute("SET FOREIGN_KEY_CHECKS = 0;");
        $this->table('gk-moves', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
        ->dropForeignKey('geokret', '`gk-moves_ibfk_1`')
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-moves_ibfk_3',
                'update' => 'NO_ACTION',
                'delete' => 'CASCADE',
            ])
            ->save();
        $this->execute("SET FOREIGN_KEY_CHECKS = 1;");
        $this->execute("SET UNIQUE_CHECKS = 1;");
    }
}
