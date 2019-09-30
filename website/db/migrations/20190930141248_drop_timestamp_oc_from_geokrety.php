<?php

use Phinx\Db\Adapter\MysqlAdapter;

class DropTimestampOcFromGeokrety extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("SET UNIQUE_CHECKS = 0;");
        $this->execute("SET FOREIGN_KEY_CHECKS = 0;");
        $this->table('gk-geokrety', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
        ->changeColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'avatar',
            ])
        ->changeColumn('updated_on_datetime', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
            ->removeColumn('timestamp_oc')
            ->save();
        $this->execute("SET FOREIGN_KEY_CHECKS = 1;");
        $this->execute("SET UNIQUE_CHECKS = 1;");
    }
}
