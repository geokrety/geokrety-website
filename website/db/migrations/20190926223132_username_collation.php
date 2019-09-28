<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UsernameCollation extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("SET UNIQUE_CHECKS = 0;");
        $this->execute("SET FOREIGN_KEY_CHECKS = 0;");
        $this->table('gk-users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
        ->changeColumn('username', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->save();
        $this->execute("SET FOREIGN_KEY_CHECKS = 1;");
        $this->execute("SET UNIQUE_CHECKS = 1;");
    }
}
