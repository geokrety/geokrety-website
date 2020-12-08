<?php

use Phinx\Migration\AbstractMigration;

class SocialAuth extends AbstractMigration {
    public function change() {
        $table_providers = $this->table('gk_social_auth_providers');
        $table_providers->addColumn('name', 'string', ['null' => false, 'limit' => 128])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addIndex(['name'])
            ->create();
        if ($this->isMigratingUp()) {
            $table_providers->insert([
                ['name' => 'Google'],
                ['name' => 'Facebook'],
            ])->save();
        }

        $table = $this->table('gk_users_social_auth', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('user', 'biginteger', ['null' => false])
            ->addForeignKey('user', 'gk_users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addColumn('provider', 'integer', ['null' => false])
            ->addForeignKey('provider', 'gk_social_auth_providers', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addColumn('uid', 'text', ['null' => false])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addIndex(['uid'])
            ->addIndex(['user', 'provider'], ['unique' => true])
            ->create();
    }
}
