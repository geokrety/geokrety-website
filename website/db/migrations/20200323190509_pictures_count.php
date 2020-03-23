<?php

use Phinx\Db\Adapter\MysqlAdapter;

class PicturesCount extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute('SET unique_checks=0; SET foreign_key_checks=0;');
        $this->table('gk-users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('pictures_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => 'Attached avatar count',
                'after' => 'avatar',
            ])
            ->changeColumn('last_mail_datetime', 'datetime', [
                'null' => true,
                'after' => 'pictures_count',
            ])
            ->changeColumn('last_login_datetime', 'datetime', [
                'null' => true,
                'after' => 'last_mail_datetime',
            ])
            ->changeColumn('terms_of_use_datetime', 'datetime', [
                'null' => false,
                'comment' => 'Acceptation date',
                'after' => 'last_login_datetime',
            ])
            ->changeColumn('secid', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'connect by other applications',
                'after' => 'terms_of_use_datetime',
            ])
            ->save();
        $this->table('gk-geokrety', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('pictures_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'caches_count',
            ])
            ->changeColumn('last_position', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'pictures_count',
            ])
            ->changeColumn('last_log', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'last_position',
            ])
            ->changeColumn('holder', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'comment' => 'In the hands of user',
                'after' => 'last_log',
            ])
            ->changeColumn('missing', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'holder',
            ])
            ->changeColumn('type', 'enum', [
                'null' => false,
                'limit' => 1,
                'values' => ['0', '1', '2', '3', '4'],
                'after' => 'missing',
            ])
            ->changeColumn('avatar', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'type',
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
            ->save();
        $this->table('gk-moves', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->changeColumn('pictures_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'comment',
            ])
            ->save();
        $this->execute('SET unique_checks=1; SET foreign_key_checks=1;');
    }
}
