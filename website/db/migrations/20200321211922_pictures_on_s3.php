<?php

use Phinx\Db\Adapter\MysqlAdapter;

class PicturesOnS3 extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute('SET unique_checks=0; SET foreign_key_checks=0;');
        $this->table('gk-geokrety', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->changeColumn('last_position', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'caches_count',
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
            ->removeColumn('pictures_count')
            ->addForeignKey('avatar', 'gk-pictures', 'id', [
                'constraint' => 'gk-geokrety_ibfk_3',
                'update' => 'RESTRICT',
                'delete' => 'SET_NULL',
            ])
            ->save();
        $this->table('gk-pictures', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('bucket', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'author',
            ])
            ->changeColumn('key', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'bucket',
            ])
            ->changeColumn('type', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'key',
            ])
            ->changeColumn('move', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'type',
            ])
            ->changeColumn('geokret', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'move',
            ])
            ->changeColumn('user', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'geokret',
            ])
            ->changeColumn('filename', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user',
            ])
            ->changeColumn('caption', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'filename',
            ])
            ->changeColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'caption',
            ])
            ->changeColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
            ->changeColumn('uploaded_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'updated_on_datetime',
            ])
            ->save();
        $this->execute('SET unique_checks=1; SET foreign_key_checks=1;');
    }
}
