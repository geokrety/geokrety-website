<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UserAvatar extends Phinx\Migration\AbstractMigration
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
            ->addColumn('avatar', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'statpic_template_id',
            ])
            ->changeColumn('last_mail_datetime', 'datetime', [
                'null' => true,
                'after' => 'avatar',
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
            ->addIndex(['avatar'], [
                'name' => 'avatar',
                'unique' => false,
            ])
            ->addForeignKey('avatar', 'gk-pictures', 'id', [
                'constraint' => 'gk-users_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'SET_NULL',
            ])
            ->save();
        $this->execute('SET unique_checks=1; SET foreign_key_checks=1;');
    }
}
