<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SiteSettings extends AbstractMigration {
    public function up(): void {
        $table_ss_parameters = $this->table('geokrety.gk_site_settings_parameters', ['id' => false, 'primary_key' => 'name']);
        $table_ss_parameters->addColumn('name', Phinx\Util\Literal::from('CHARACTER VARYING(64)'), ['null' => false])
            ->addColumn('type', Phinx\Util\Literal::from('CHARACTER VARYING(32)'), ['null' => false, 'default' => 'string'])
            ->addColumn('default', Phinx\Util\Literal::from('CHARACTER VARYING(256)'), ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->save();

        $table_settings = $this->table('geokrety.gk_site_settings', ['primary_key' => 'id']);
        $table_settings->addColumn('name', Phinx\Util\Literal::from('CHARACTER VARYING(64)'), ['null' => false])
            ->addColumn('value', Phinx\Util\Literal::from('CHARACTER VARYING(256)'), ['null' => true])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addForeignKey('name', 'geokrety.gk_site_settings_parameters', 'name', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['name'], ['unique' => true])
            ->save();

        if ($this->isMigratingUp()) {
            $table_ss_parameters->insert([
                ['name' => 'ADMIN_EMAIL_BCC_ENABLED', 'type' => 'bool', 'default' => 'false', 'description' => 'When enabled, admin will be set as bcc for all mails'],
            ])->save();
            $table_settings->insert([
                ['name' => 'ADMIN_EMAIL_BCC_ENABLED', 'value' => 'false'],
            ])->save();
        }
    }

    public function down(): void {
        $this->table('geokrety.gk_site_settings')
            ->drop()
            ->save();
        $this->table('geokrety.gk_site_settings_parameters')
            ->drop()
            ->save();
    }
}
