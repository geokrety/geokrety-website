<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialUserSettings extends AbstractMigration {
    public function up(): void {
        $table_setting_params = $this->table('geokrety.gk_users_settings_parameters', ['id' => false, 'primary_key' => 'name']);
        $table_setting_params->addColumn('name', \Phinx\Util\Literal::from('CHARACTER VARYING(64)'), ['null' => false])
            ->addColumn('type', \Phinx\Util\Literal::from('CHARACTER VARYING(32)'), ['null' => false, 'default' => 'string'])
            ->addColumn('default', \Phinx\Util\Literal::from('CHARACTER VARYING(256)'), ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->save();

        $this->table('geokrety.gk_users_settings', ['primary_key' => 'id'])
            ->addColumn('user', 'biginteger', ['null' => false])
            ->addColumn('name', \Phinx\Util\Literal::from('CHARACTER VARYING(64)'), ['null' => false])
            ->addColumn('value', \Phinx\Util\Literal::from('CHARACTER VARYING(256)'), ['null' => true])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addForeignKey('user', 'gk_users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('name', 'geokrety.gk_users_settings_parameters', 'name', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['user', 'name'], ['unique' => true])
            ->save();

        if ($this->isMigratingUp()) {
            $table_setting_params->insert([
                ['name' => 'TRACKING_OPT_OUT', 'type' => 'bool', 'default' => 'false', 'description' => 'Opt-out from site usage analytics'],
            ])->save();
        }
    }

    public function down(): void {
        $this->table('geokrety.gk_users_settings')
            ->drop()
            ->save();
        $this->table('geokrety.gk_users_settings_parameters')
            ->drop()
            ->save();
    }
}
