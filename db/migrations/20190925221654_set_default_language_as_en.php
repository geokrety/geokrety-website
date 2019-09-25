<?php

use Phinx\Db\Adapter\MysqlAdapter;

class SetDefaultLanguageAsEn extends Phinx\Migration\AbstractMigration
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
        ->changeColumn('preferred_language', 'string', [
                'null' => false,
                'default' => 'en',
                'limit' => 2,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'registration_ip',
            ])
            ->save();
        $this->execute("SET FOREIGN_KEY_CHECKS = 1;");
        $this->execute("SET UNIQUE_CHECKS = 1;");
    }
}
