<?php

use Phinx\Db\Adapter\MysqlAdapter;

class SessionsInDb extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute('SET unique_checks=0; SET foreign_key_checks=0;');
        $this->table('sessions', [
                'id' => false,
                'primary_key' => ['session_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('session_id', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
            ])
            ->addColumn('data', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'session_id',
            ])
            ->addColumn('ip', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'data',
            ])
            ->addColumn('agent', 'string', [
                'null' => true,
                'limit' => 300,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'ip',
            ])
            ->addColumn('stamp', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'agent',
            ])
            ->create();
        $this->execute('SET unique_checks=1; SET foreign_key_checks=1;');
    }
}
