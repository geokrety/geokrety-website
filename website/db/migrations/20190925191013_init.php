<?php

use Phinx\Db\Adapter\MysqlAdapter;

class Init extends Phinx\Migration\AbstractMigration {
    public function change() {
        $this->execute('SET UNIQUE_CHECKS = 0;');
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8mb4';");
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_ci';");
        $this->table('gk-users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('username', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_polish_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('old_password', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'This hash is not used anymore',
                'after' => 'username',
            ])
            ->addColumn('password', 'string', [
                'null' => false,
                'limit' => 120,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'old_password',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'password',
            ])
            ->addColumn('account_valid', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0=unconfirmed 1=confirmed',
                'after' => 'email',
            ])
            ->addColumn('email_invalid', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '* 0 ok * 1 blocked * 2 autoresponder',
                'after' => 'account_valid',
            ])
            ->addColumn('joined_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'email_invalid',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'joined_on_datetime',
            ])
            ->addColumn('daily_mails', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_on_datetime',
            ])
            ->addColumn('registration_ip', 'string', [
                'null' => false,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'daily_mails',
            ])
            ->addColumn('preferred_language', 'string', [
                'null' => true,
                'limit' => 2,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'registration_ip',
            ])
            ->addColumn('home_latitude', 'double', [
                'null' => true,
                'after' => 'preferred_language',
            ])
            ->addColumn('home_longitude', 'double', [
                'null' => true,
                'after' => 'home_latitude',
            ])
            ->addColumn('observation_area', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_SMALL,
                'after' => 'home_longitude',
            ])
            ->addColumn('home_country', 'char', [
                'null' => true,
                'limit' => 3,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'observation_area',
            ])
            ->addColumn('daily_mails_hour', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'home_country',
            ])
            ->addColumn('statpic_template_id', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'daily_mails_hour',
            ])
            ->addColumn('last_mail_datetime', 'datetime', [
                'null' => true,
                'after' => 'statpic_template_id',
            ])
            ->addColumn('last_login_datetime', 'datetime', [
                'null' => true,
                'after' => 'last_mail_datetime',
            ])
            ->addColumn('terms_of_use_datetime', 'datetime', [
                'null' => false,
                'comment' => 'Acceptation date',
                'after' => 'last_login_datetime',
            ])
            ->addColumn('secid', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'connect by other applications',
                'after' => 'terms_of_use_datetime',
            ])
        ->addIndex(['username'], [
                'name' => 'user',
                'unique' => true,
            ])
        ->addIndex(['secid'], [
                'name' => 'secid',
                'unique' => false,
            ])
        ->addIndex(['last_login_datetime'], [
                'name' => 'ostatni_login',
                'unique' => false,
            ])
        ->addIndex(['email_invalid'], [
                'name' => 'email_invalid',
                'unique' => false,
            ])
        ->addIndex(['email'], [
                'name' => 'email',
                'unique' => false,
            ])
        ->addIndex(['username'], [
                'name' => 'username',
                'unique' => false,
            ])
        ->addIndex(['username', 'email'], [
                'name' => 'username_email',
                'unique' => false,
            ])
            ->create();
        $this->table('gk-account-activation', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 60,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('user', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'token',
            ])
            ->addColumn('used', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0=unused 1=validated 2=expired',
                'after' => 'user',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'used',
            ])
            ->addColumn('used_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'created_on_datetime',
            ])
            ->addColumn('requesting_ip', 'string', [
                'null' => false,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'used_on_datetime',
            ])
            ->addColumn('validating_ip', 'string', [
                'null' => true,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'requesting_ip',
            ])
        ->addIndex(['user'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addIndex(['used', 'created_on_datetime'], [
                'name' => 'used_created_on_datetime',
                'unique' => false,
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-account-activation_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('gk-activation-codes', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 60,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'token',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
                'after' => 'updated_on_datetime',
            ])
            ->create();
        $this->table('gk-badges', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'badges for the users',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('holder', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('description', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'holder',
            ])
            ->addColumn('filename', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('awarded_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'filename',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'awarded_on_datetime',
            ])
        ->addIndex(['awarded_on_datetime'], [
                'name' => 'timestamp',
                'unique' => false,
            ])
        ->addIndex(['holder'], [
                'name' => 'userid',
                'unique' => false,
            ])
        ->addForeignKey('holder', 'gk-users', 'id', [
                'constraint' => 'gk-badges_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-email-activation', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 60,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('revert_token', 'string', [
                'null' => false,
                'limit' => 60,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'token',
            ])
            ->addColumn('user', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'revert_token',
            ])
            ->addColumn('previous_email', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'Store the previous in case of needed rollback',
                'after' => 'user',
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 150,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'previous_email',
            ])
            ->addColumn('used', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0=unused 1=validated 2=refused 3=expired',
                'after' => 'email',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'used',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
            ->addColumn('used_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'updated_on_datetime',
            ])
            ->addColumn('reverted_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'used_on_datetime',
            ])
            ->addColumn('requesting_ip', 'string', [
                'null' => true,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'reverted_on_datetime',
            ])
            ->addColumn('updating_ip', 'string', [
                'null' => true,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'requesting_ip',
            ])
            ->addColumn('reverting_ip', 'string', [
                'null' => true,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'updating_ip',
            ])
        ->addIndex(['user'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addIndex(['token'], [
                'name' => 'token',
                'unique' => false,
            ])
        ->addIndex(['used', 'created_on_datetime', 'token'], [
                'name' => 'used_created_on_datetime_token',
                'unique' => false,
            ])
        ->addIndex(['used', 'used_on_datetime', 'revert_token'], [
                'name' => 'used_used_on_datetime_revert_token',
                'unique' => false,
            ])
        ->addIndex(['used', 'created_on_datetime', 'used_on_datetime'], [
                'name' => 'used_created_on_datetime_used_on_datetime',
                'unique' => false,
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-email-activation_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-email-activation_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('gk-geokrety', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('gkid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'comment' => 'The real GK id : https://stackoverflow.com/a/33791018/944936',
                'after' => 'id',
            ])
            ->addColumn('tracking_code', 'string', [
                'null' => false,
                'limit' => 9,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'gkid',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 75,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'tracking_code',
            ])
            ->addColumn('mission', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_MEDIUM,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('owner', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'mission',
            ])
            ->addColumn('distance', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '10',
                'signed' => false,
                'after' => 'owner',
            ])
            ->addColumn('caches_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_SMALL,
                'after' => 'distance',
            ])
            ->addColumn('pictures_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_SMALL,
                'after' => 'caches_count',
            ])
            ->addColumn('last_position', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'pictures_count',
            ])
            ->addColumn('last_log', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'last_position',
            ])
            ->addColumn('holder', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'comment' => 'In the hands of user',
                'after' => 'last_log',
            ])
            ->addColumn('missing', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'holder',
            ])
            ->addColumn('type', 'enum', [
                'null' => false,
                'limit' => 1,
                'values' => ['0', '1', '2', '3', '4'],
                'after' => 'missing',
            ])
            ->addColumn('avatar', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'type',
            ])
            ->addColumn('timestamp_oc', 'datetime', [
                'null' => false,
                'comment' => 'Unused?',
                'after' => 'avatar',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'timestamp_oc',
            ])
            ->addColumn('updated_on_datetime', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
        ->addIndex(['tracking_code'], [
                'name' => 'id',
                'unique' => true,
            ])
        ->addIndex(['owner'], [
                'name' => 'owner',
                'unique' => false,
            ])
        ->addIndex(['tracking_code'], [
                'name' => 'nr',
                'unique' => false,
            ])
        ->addIndex(['last_position'], [
                'name' => 'ost_pozycja_id',
                'unique' => false,
            ])
        ->addIndex(['avatar'], [
                'name' => 'avatarid',
                'unique' => false,
            ])
        ->addIndex(['last_log'], [
                'name' => 'ost_log_id',
                'unique' => false,
            ])
        ->addIndex(['holder'], [
                'name' => 'hands_of_index',
                'unique' => false,
            ])
        ->addIndex(['type'], [
                'name' => 'id_typ',
                'unique' => false,
            ])
        ->addForeignKey('holder', 'gk-users', 'id', [
                'constraint' => 'gk-geokrety_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('owner', 'gk-users', 'id', [
                'constraint' => 'gk-geokrety_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-geokrety-rating', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'GK ratings',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'comment' => 'id kreta',
                'after' => 'id',
            ])
            ->addColumn('user', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'comment' => 'id usera',
                'after' => 'geokret',
            ])
            ->addColumn('rate', 'double', [
                'null' => false,
                'default' => '0',
                'comment' => 'single rating (number of stars)',
                'after' => 'user',
            ])
            ->addColumn('rated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'rate',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'rated_on_datetime',
            ])
        ->addIndex(['geokret'], [
                'name' => 'geokret',
                'unique' => false,
            ])
        ->addIndex(['user'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-geokrety-rating_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-geokrety-rating_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-mail', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 10,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('from', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'token',
            ])
            ->addColumn('to', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'from',
            ])
            ->addColumn('subject', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'to',
            ])
            ->addColumn('content', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_MEDIUM,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject',
            ])
            ->addColumn('sent_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'content',
            ])
            ->addColumn('ip', 'string', [
                'null' => false,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'sent_on_datetime',
            ])
        ->addIndex(['id'], [
                'name' => 'id_maila',
                'unique' => true,
            ])
        ->addIndex(['from'], [
                'name' => 'from',
                'unique' => false,
            ])
        ->addIndex(['to'], [
                'name' => 'to',
                'unique' => false,
            ])
        ->addForeignKey('from', 'gk-users', 'id', [
                'constraint' => 'gk-mail_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('to', 'gk-users', 'id', [
                'constraint' => 'gk-mail_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-move-comments', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('move', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'move',
            ])
            ->addColumn('author', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'geokret',
            ])
            ->addColumn('content', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'author',
            ])
            ->addColumn('type', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0=comment, 1=missing',
                'after' => 'content',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'type',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
        ->addIndex(['geokret'], [
                'name' => 'kret_id',
                'unique' => false,
            ])
        ->addIndex(['author'], [
                'name' => 'user_id',
                'unique' => false,
            ])
        ->addIndex(['move'], [
                'name' => 'ruch_id',
                'unique' => false,
            ])
        ->addForeignKey('move', 'gk-moves', 'id', [
                'constraint' => 'gk-move-comments_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-move-comments_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
        ->addForeignKey('author', 'gk-users', 'id', [
                'constraint' => 'gk-move-comments_ibfk_3',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('gk-moves', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('lat', 'double', [
                'null' => true,
                'after' => 'geokret',
            ])
            ->addColumn('lon', 'double', [
                'null' => true,
                'after' => 'lat',
            ])
            ->addColumn('alt', 'integer', [
                'null' => true,
                'default' => '-32768',
                'limit' => '5',
                'after' => 'lon',
            ])
            ->addColumn('country', 'string', [
                'null' => true,
                'limit' => 3,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1',
                'after' => 'alt',
            ])
            ->addColumn('distance', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'country',
            ])
            ->addColumn('waypoint', 'string', [
                'null' => true,
                'limit' => 10,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'distance',
            ])
            ->addColumn('author', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'waypoint',
            ])
            ->addColumn('comment', 'string', [
                'null' => true,
                'limit' => 5120,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'author',
            ])
            ->addColumn('pictures_count', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'comment',
            ])
            ->addColumn('comments_count', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_SMALL,
                'after' => 'pictures_count',
            ])
            ->addColumn('logtype', 'enum', [
                'null' => false,
                'limit' => 1,
                'values' => ['0', '1', '2', '3', '4', '5', '6'],
                'comment' => '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip',
                'after' => 'comments_count',
            ])
            ->addColumn('username', 'string', [
                'null' => true,
                'limit' => 20,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'logtype',
            ])
            ->addColumn('app', 'string', [
                'null' => true,
                'limit' => 16,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'source of the log',
                'after' => 'username',
            ])
            ->addColumn('app_ver', 'string', [
                'null' => true,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'application version/codename',
                'after' => 'app',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'app_ver',
            ])
            ->addColumn('moved_on_datetime', 'datetime', [
                'null' => false,
                'comment' => 'The move as configured by user',
                'after' => 'created_on_datetime',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'moved_on_datetime',
            ])
        ->addIndex(['geokret'], [
                'name' => 'id_2',
                'unique' => false,
            ])
        ->addIndex(['waypoint'], [
                'name' => 'waypoint',
                'unique' => false,
            ])
        ->addIndex(['author'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addIndex(['lat'], [
                'name' => 'lat',
                'unique' => false,
            ])
        ->addIndex(['lon'], [
                'name' => 'lon',
                'unique' => false,
            ])
        ->addIndex(['logtype'], [
                'name' => 'logtype',
                'unique' => false,
            ])
        ->addIndex(['created_on_datetime'], [
                'name' => 'data',
                'unique' => false,
            ])
        ->addIndex(['moved_on_datetime'], [
                'name' => 'data_dodania',
                'unique' => false,
            ])
        ->addIndex(['updated_on_datetime'], [
                'name' => 'timestamp',
                'unique' => false,
            ])
        ->addIndex(['alt'], [
                'name' => 'alt',
                'unique' => false,
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-moves_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('author', 'gk-users', 'id', [
                'constraint' => 'gk-moves_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-news', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('title', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('content', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('author_name', 'string', [
                'null' => true,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'content',
            ])
            ->addColumn('author', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'author_name',
            ])
            ->addColumn('comments_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_SMALL,
                'after' => 'author',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'comments_count',
            ])
            ->addColumn('last_commented_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
        ->addIndex(['created_on_datetime'], [
                'name' => 'date',
                'unique' => false,
            ])
        ->addIndex(['author'], [
                'name' => 'userid',
                'unique' => false,
            ])
        ->addForeignKey('author', 'gk-users', 'id', [
                'constraint' => 'gk-news_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('author', 'gk-users', 'id', [
                'constraint' => 'gk-news_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-news-comments', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('news', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('author', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'news',
            ])
            ->addColumn('content', 'string', [
                'null' => false,
                'limit' => 1000,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'author',
            ])
            ->addColumn('icon', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'content',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'icon',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
        ->addIndex(['news'], [
                'name' => 'news',
                'unique' => false,
            ])
        ->addIndex(['author'], [
                'name' => 'author',
                'unique' => false,
            ])
        ->addForeignKey('news', 'gk-news', 'id', [
                'constraint' => 'gk-news-comments_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('news', 'gk-news', 'id', [
                'constraint' => 'gk-news-comments_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('author', 'gk-users', 'id', [
                'constraint' => 'gk-news-comments_ibfk_3',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('gk-news-comments-access', [
                'id' => false,
                'primary_key' => ['news', 'user'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('news', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('user', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'news',
            ])
            ->addColumn('last_read_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'user',
            ])
            ->addColumn('last_post_datetime', 'datetime', [
                'null' => true,
                'after' => 'last_read_datetime',
            ])
            ->addColumn('subscribed', 'enum', [
                'null' => false,
                'limit' => 1,
                'values' => ['0', '1'],
                'after' => 'last_post_datetime',
            ])
        ->addIndex(['id'], [
                'name' => 'id',
                'unique' => true,
            ])
        ->addIndex(['user'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addForeignKey('news', 'gk-news', 'id', [
                'constraint' => 'gk-news-comments-access_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-news-comments-access_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('gk-owner-codes', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 20,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'geokret',
            ])
            ->addColumn('generated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'token',
            ])
            ->addColumn('claimed_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'generated_on_datetime',
            ])
            ->addColumn('user', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'claimed_on_datetime',
            ])
        ->addIndex(['geokret'], [
                'name' => 'kret_id',
                'unique' => false,
            ])
        ->addIndex(['token'], [
                'name' => 'code',
                'unique' => false,
            ])
        ->addIndex(['user'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-owner-codes_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-owner-codes_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-owner-codes_ibfk_3',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-owner-codes_ibfk_4',
                'update' => 'RESTRICT',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('gk-password-tokens', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Retrieve user password',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 60,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('user', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'token',
            ])
            ->addColumn('used', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0=unused 1=used',
                'after' => 'user',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'used',
            ])
            ->addColumn('used_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'created_on_datetime',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'used_on_datetime',
            ])
            ->addColumn('requesting_ip', 'string', [
                'null' => false,
                'limit' => 46,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'updated_on_datetime',
            ])
        ->addIndex(['user'], [
                'name' => 'user',
                'unique' => false,
            ])
        ->addIndex(['token', 'used'], [
                'name' => 'token_used',
                'unique' => false,
            ])
        ->addIndex(['created_on_datetime'], [
                'name' => 'created_on_datetime',
                'unique' => false,
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-password-tokens_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('gk-pictures', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('typ', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'id',
            ])
            ->addColumn('move', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'typ',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'move',
            ])
            ->addColumn('user', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'geokret',
            ])
            ->addColumn('filename', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user',
            ])
            ->addColumn('caption', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'filename',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'caption',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
        ->addIndex(['id'], [
                'name' => 'obrazekid',
                'unique' => true,
            ])
        ->addIndex(['geokret', 'typ'], [
                'name' => 'idkreta_typ',
                'unique' => false,
            ])
        ->addIndex(['move'], [
                'name' => 'id',
                'unique' => false,
            ])
        ->addIndex(['geokret'], [
                'name' => 'id_kreta',
                'unique' => false,
            ])
            ->create();
        $this->table('gk-races', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'race definitions',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'Creation date',
                'after' => 'id',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_on_datetime',
            ])
            ->addColumn('organizer', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'updated_on_datetime',
            ])
            ->addColumn('private', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0 = public, 1 = private',
                'after' => 'organizer',
            ])
            ->addColumn('password', 'string', [
                'null' => false,
                'limit' => 16,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'password to join the race',
                'after' => 'private',
            ])
            ->addColumn('title', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'password',
            ])
            ->addColumn('description', 'string', [
                'null' => false,
                'limit' => 5120,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('start_on_datetime', 'datetime', [
                'null' => true,
                'comment' => 'Race start date',
                'after' => 'description',
            ])
            ->addColumn('end_on_datetime', 'datetime', [
                'null' => true,
                'comment' => 'Race end date',
                'after' => 'start_on_datetime',
            ])
            ->addColumn('type', 'string', [
                'null' => false,
                'limit' => 16,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'Type of race',
                'after' => 'end_on_datetime',
            ])
            ->addColumn('waypoint', 'string', [
                'null' => false,
                'limit' => 16,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('target_lat', 'double', [
                'null' => true,
                'after' => 'waypoint',
            ])
            ->addColumn('target_lon', 'double', [
                'null' => true,
                'after' => 'target_lat',
            ])
            ->addColumn('target_dist', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'target distance',
                'after' => 'target_lon',
            ])
            ->addColumn('target_caches', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'targeted number of caches',
                'after' => 'target_dist',
            ])
            ->addColumn('status', 'integer', [
                'null' => false,
                'limit' => '1',
                'comment' => 'race status. 0 = initialized, 1 = persist, 2 = finite, 3 = finished but logs flow down',
                'after' => 'target_caches',
            ])
        ->addIndex(['organizer'], [
                'name' => 'organizer',
                'unique' => false,
            ])
        ->addForeignKey('organizer', 'gk-users', 'id', [
                'constraint' => 'gk-races_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-races-krety', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => "uczestnicy rajd\xc3\xb3w",
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('race', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'race',
            ])
            ->addColumn('initial_distance', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'geokret',
            ])
            ->addColumn('initial_caches_count', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'initial_distance',
            ])
            ->addColumn('distance_to_destination', 'double', [
                'null' => true,
                'after' => 'initial_caches_count',
            ])
            ->addColumn('joined_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'distance_to_destination',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'joined_on_datetime',
            ])
            ->addColumn('finished_on_datetime', 'datetime', [
                'null' => true,
                'after' => 'updated_on_datetime',
            ])
            ->addColumn('finish_distance', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'finished_on_datetime',
            ])
            ->addColumn('finish_caches_count', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'finish_distance',
            ])
            ->addColumn('finish_lat', 'double', [
                'null' => true,
                'after' => 'finish_caches_count',
            ])
            ->addColumn('finish_lon', 'double', [
                'null' => true,
                'after' => 'finish_lat',
            ])
        ->addIndex(['id'], [
                'name' => 'raceGkId',
                'unique' => true,
            ])
        ->addIndex(['race'], [
                'name' => 'race',
                'unique' => false,
            ])
        ->addIndex(['geokret'], [
                'name' => 'geokret',
                'unique' => false,
            ])
        ->addForeignKey('race', 'gk-races', 'id', [
                'constraint' => 'gk-races-krety_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-races-krety_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-statystyki-dzienne', [
                'id' => false,
                'primary_key' => ['data'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => "informacje nt. przyrostu zmiennych dzie\xc5\x84 po dniu",
                'row_format' => 'COMPACT',
            ])
            ->addColumn('data', 'date', [
                'null' => false,
            ])
            ->addColumn('unix_timestamp', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'data',
            ])
            ->addColumn('dzien', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'unix_timestamp',
            ])
            ->addColumn('gk', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'dzien',
            ])
            ->addColumn('gk_', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'gk',
            ])
            ->addColumn('gk_zakopane_', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'gk_',
            ])
            ->addColumn('procent_zakopanych', 'float', [
                'null' => false,
                'after' => 'gk_zakopane_',
            ])
            ->addColumn('users', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'procent_zakopanych',
            ])
            ->addColumn('users_', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users',
            ])
            ->addColumn('ruchow', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_',
            ])
            ->addColumn('ruchow_', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'ruchow',
            ])
        ->addIndex(['data'], [
                'name' => 'data',
                'unique' => true,
            ])
            ->create();
        $this->table('gk-wartosci', [
                'id' => false,
                'primary_key' => ['name'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('value', 'float', [
                'null' => false,
                'after' => 'name',
            ])
        ->addIndex(['name'], [
                'name' => 'name',
                'unique' => true,
            ])
            ->create();
        $this->table('gk-watched', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('geokret', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'after' => 'user',
            ])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'geokret',
            ])
        ->addIndex(['user'], [
                'name' => 'userid',
                'unique' => false,
            ])
        ->addIndex(['geokret'], [
                'name' => 'id',
                'unique' => false,
            ])
        ->addForeignKey('user', 'gk-users', 'id', [
                'constraint' => 'gk-watched_ibfk_1',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
        ->addForeignKey('geokret', 'gk-geokrety', 'id', [
                'constraint' => 'gk-watched_ibfk_2',
                'update' => 'RESTRICT',
                'delete' => 'RESTRICT',
            ])
            ->create();
        $this->table('gk-waypointy', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('waypoint', 'string', [
                'null' => false,
                'default' => '',
                'limit' => 11,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('lat', 'double', [
                'null' => true,
                'after' => 'waypoint',
            ])
            ->addColumn('lon', 'double', [
                'null' => true,
                'after' => 'lat',
            ])
            ->addColumn('alt', 'integer', [
                'null' => false,
                'default' => '-32768',
                'limit' => '5',
                'after' => 'lon',
            ])
            ->addColumn('country', 'char', [
                'null' => true,
                'limit' => 3,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'country code as ISO 3166-1 alpha-2',
                'after' => 'alt',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'country',
            ])
            ->addColumn('owner', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'owner',
            ])
            ->addColumn('country_name', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'full English country name',
                'after' => 'type',
            ])
            ->addColumn('link', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'country_name',
            ])
            ->addColumn('status', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'link',
            ])
            ->addColumn('added_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'status',
            ])
            ->addColumn('updated_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'added_on_datetime',
            ])
        ->addIndex(['waypoint'], [
                'name' => 'waypoint',
                'unique' => true,
            ])
        ->addIndex(['waypoint'], [
                'name' => 'waypoint_2',
                'unique' => false,
            ])
            ->create();
        $this->table('gk-waypointy-country', [
                'id' => false,
                'primary_key' => ['kraj'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('kraj', 'string', [
                'null' => false,
                'limit' => 191,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('country', 'string', [
                'null' => true,
                'limit' => 191,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'kraj',
            ])
        ->addIndex(['kraj'], [
                'name' => 'unique_kraj',
                'unique' => true,
            ])
            ->create();
        $this->table('gk-waypointy-sync', [
                'id' => false,
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Last synchronization time for GC services',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('service_id', 'string', [
                'null' => false,
                'limit' => 5,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('last_update', 'string', [
                'null' => true,
                'limit' => 15,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'service_id',
            ])
            ->create();
        $this->table('gk-waypointy-type', [
                'id' => false,
                'primary_key' => ['typ'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('typ', 'string', [
                'null' => false,
                'limit' => 191,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('cache_type', 'string', [
                'null' => true,
                'limit' => 191,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'typ',
            ])
        ->addIndex(['typ'], [
                'name' => 'unique_typ',
                'unique' => true,
            ])
            ->create();

        $this->execute("INSERT INTO `gk-waypointy-country` (`kraj`, `country`) VALUES
            ('Afghanistan',	'Afghanistan'),
            ('Ägypten',	'Egypt'),
            ('Albania',	'Albania'),
            ('Albanien',	'Albania'),
            ('Amerikanisch-Ozeanien',	'American Samoa'),
            ('Argentinien',	'Argentina'),
            ('Argentyna',	'Argentina'),
            ('Armenia',	'Armenia'),
            ('AT',	'Austria'),
            ('AU',	'Australia'),
            ('Australien',	'Australia'),
            ('Austria',	'Austria'),
            ('Bahamas',	'Bahamas'),
            ('Belarus (Weißrußland)',	'Belarus'),
            ('Belgia',	'Belgium'),
            ('Belgien',	'Belgium'),
            ('Białoruś',	'Belarus'),
            ('Bosnien-Herzegowina',	'Bosnia and Herzegovina'),
            ('Botsuana',	'Botswana'),
            ('Brasilien',	'Brazil'),
            ('Bulgarien',	'Bulgaria'),
            ('Bułgaria',	'Bulgaria'),
            ('CA',	'Canada'),
            ('Canada',	'Canada'),
            ('Česká Republika',	'Czech Republic'),
            ('Chile',	'Chile'),
            ('Chiny',	'China'),
            ('Chorwacja',	'Croatia'),
            ('Costa Rica',	'Costa Rica'),
            ('Cypr',	'Cyprus'),
            ('CZ',	'Czech Republic'),
            ('Czeska Republika',	'Czech Republic'),
            ('Dänemark',	'Denmark'),
            ('Dania',	'Denmark'),
            ('DE',	'Germany'),
            ('Demokratische Volksrepublik Korea',	'North Korea'),
            ('Denmark',	'Denmark'),
            ('Deutschland',	'Germany'),
            ('Dominikanische Republik',	'Dominican Republic'),
            ('Ecuador',	'Ecuador'),
            ('Egipt',	'Egypt'),
            ('El Salvador',	'El Salvador'),
            ('ES',	'Spain'),
            ('Estland',	'Estonia'),
            ('Estonia',	'Estonia'),
            ('Falklandinseln',	'Falkland Islands'),
            ('Färöer (zu Dänemark)',	'Faroe Islands'),
            ('Finlandia',	'Finland'),
            ('Finnland',	'Finland'),
            ('France',	'France'),
            ('Francja',	'France'),
            ('Frankreich',	'France'),
            ('Georgien',	'Georgia'),
            ('Germany',	'Germany'),
            ('Gibraltar',	'Gibraltar'),
            ('Granada',	'Granada'),
            ('Grecja',	'Greece'),
            ('Greenland',	'Greenland'),
            ('Griechenland',	'Greece'),
            ('Grönland',	'Greenland'),
            ('Großbritannien',	'United Kingdom'),
            ('Guatemala',	'Guatemala'),
            ('Hiszpania',	'Spain'),
            ('Holandia',	'Netherlands'),
            ('Honduras',	'Honduras'),
            ('HR',	'Croatia'),
            ('ID',	'Indonesia'),
            ('IN',	'India'),
            ('Indie',	'India'),
            ('Indien',	'India'),
            ('Irland',	'Ireland'),
            ('Irlandia',	'Ireland'),
            ('Island',	'Iceland'),
            ('Islandia',	'Iceland'),
            ('Israel',	'Israel'),
            ('Italien',	'Italy'),
            ('Japan',	'Japan'),
            ('Jemen',	'Yemen'),
            ('Jordanien',	'Jordan'),
            ('Kambodża',	'Cambodia'),
            ('Kanada',	'Canada'),
            ('Kapverden',	'Cape Verde'),
            ('Kasachstan',	'Kazakhstan'),
            ('Kenia',	'Kenya'),
            ('KG',	'Kyrgyzstan'),
            ('Kirgistan',	'Kyrgyzstan'),
            ('Kolumbien',	'Colombia'),
            ('Kroatien',	'Croatia'),
            ('Kuba',	'Cuba'),
            ('Laos',	'Laos'),
            ('Lettland',	'Latvia'),
            ('Liechtenstein',	'Liechtenstein'),
            ('Litauen',	'Lithuania'),
            ('Litwa',	'Lithuania'),
            ('Luxemburg',	'Luxembourg'),
            ('Łotwa',	'Latvia'),
            ('Malaysia',	'Malaysia'),
            ('Malediven',	'Maldives'),
            ('Malta',	'Malta'),
            ('Marokko',	'Morocco'),
            ('Maroko',	'Morocco'),
            ('Mauritius',	'Mauritius'),
            ('Mazedonien',	'Macedonia'),
            ('Mexico',	'Mexico'),
            ('Mexiko',	'Mexico'),
            ('Mołdawia',	'Moldova'),
            ('Monako',	'Monaco'),
            ('Montenegro',	'Montenegro'),
            ('Namibia',	'Namibia'),
            ('Nepal',	'Nepal'),
            ('Neuseeland',	'New Zealand'),
            ('Nicaragua',	'Nicaragua'),
            ('Niderlandy',	'Netherlands'),
            ('Niederlande',	'Netherlands'),
            ('Niederländische Antillen',	'Netherlands Antilles'),
            ('Niemcy',	'Germany'),
            ('NO',	'Norway'),
            ('Norwegen',	'Norway'),
            ('Norwegia',	'Norway'),
            ('Österreich',	'Austria'),
            ('Panama',	'Panama'),
            ('Papua-Neuguinea',	'Papua New Guinea'),
            ('Philippinen',	'Philippines'),
            ('PL',	'Poland'),
            ('Polen',	'Poland'),
            ('Polska',	'Poland'),
            ('Portugal',	'Portugal'),
            ('Portugalia',	'Portugal'),
            ('Rosja',	'Russia'),
            ('Ruanda',	'Rwanda'),
            ('Rumänien',	'Romania'),
            ('Rumunia',	'Romania'),
            ('Russische Föderation',	'Russia'),
            ('Sambia',	'Zambia'),
            ('Saudi-Arabien',	'Saudi Arabia'),
            ('Schweden',	'Sweden'),
            ('Schweiz',	'Switzerland'),
            ('SE',	'Sweden'),
            ('Serbien',	'Serbia'),
            ('Serbien und Montenegro',	'Serbia and Montenegro'),
            ('Seychellen',	'Seychelles'),
            ('SI',	'Slovenia'),
            ('Simbabwe',	'Zimbabwe'),
            ('SK',	'Slovakia'),
            ('Slowakai',	'Slovakia'),
            ('Slowenien',	'Slovenia'),
            ('Słowacja',	'Slovakia'),
            ('Soviet Union',	'Soviet Union'),
            ('Spain',	'Spain'),
            ('Spanien',	'Spain'),
            ('Sri Lanka',	'Sri Lanka'),
            ('Südafrika',	'South Africa'),
            ('Sudan',	'Sudan'),
            ('Südgeorgien und die Südlichen Sandwichinseln',	'South Georgia and the South Sandwich Islands'),
            ('Svalbard und Jan Mayen',	'Svalbard and Jan Mayen'),
            ('Sweden',	'Sweden'),
            ('Syrien',	'Syria'),
            ('Szwajcaria',	'Switzerland'),
            ('Szwecja',	'Sweden'),
            ('Taiwan',	'Taiwan'),
            ('Tajlandia',	'Thailand'),
            ('Tansania',	'Tanzania'),
            ('Thailand',	'Thailand'),
            ('Togo',	'Togo'),
            ('Trinidad und Tobago',	'Trinidad and Tobago'),
            ('Tschad',	'Chad'),
            ('Tschechische Republik',	'Czech Republic'),
            ('Tunesien',	'Tunisia'),
            ('Tunezja',	'Tunisia'),
            ('Turcja',	'Turkey'),
            ('Türkei',	'Turkey'),
            ('UA',	'Ukraine'),
            ('Uganda',	'Uganda'),
            ('UK',	'United Kingdom'),
            ('Ukraina',	'Ukraine'),
            ('Ukraine',	'Ukraine'),
            ('Ungarn',	'Hungary'),
            ('United States',	'United States'),
            ('US',	'United States'),
            ('USA',	'USA'),
            ('Usbekistan',	'Uzbekistan'),
            ('Vatikan (Heiliger Stuhl)',	'Holy See'),
            ('Vereinigte Arabische Emirate',	'United Arab Emirates'),
            ('Vereinigte Staaten',	'United States'),
            ('Vietnam',	'Vietnam'),
            ('Virgin Islands (U.S.)',	'United States Virgin Islands'),
            ('Volksrepublik China',	'China'),
            ('Węgry',	'Hungary'),
            ('Wielka Brytania',	'United Kingdom'),
            ('Wietnam',	'Vietnam'),
            ('Włochy',	'Italy'),
            ('Zjednoczone Emiraty Arabskie',	'United Arab Emirates'),
            ('Zypern',	'Cyprus');");

        $this->execute("INSERT INTO `gk-waypointy-type` (`typ`, `cache_type`) VALUES
            ('beweglicher Cache',	'Mobile'),
            ('BIT Cache',	'BIT Cache'),
            ('Cemetery',	'Cemetery'),
            ('Drive-In',	'Drive-In'),
            ('Drive-In-Cache',	'Drive-In'),
            ('Event',	'Event'),
            ('Event Cache',	'Event'),
            ('Event-Cache',	'Event'),
            ('Geocache',	'Traditional'),
            ('Geocache|Event Cache',	'Event'),
            ('Geocache|Multi-cache',	'Multicache'),
            ('Geocache|Mystery Cache',	'Mystery'),
            ('Geocache|Traditional Cache',	'Traditional'),
            ('Geocache|Unknown Cache',	'Unknown cache'),
            ('Geocache|Virtual Cache',	'Virtual'),
            ('Geocache|Webcam Cache',	'Webcam'),
            ('Guest Book',	'Guest Book'),
            ('Inny typ skrzynki',	'Other'),
            ('kvíz',	'Quiz'),
            ('Letterbox',	'Letterbox'),
            ('Mathe-/Physikcache',	'Math / physics cache'),
            ('Medical Facility',	'Medical Facility'),
            ('Mobilna',	'Mobile'),
            ('Moving',	'Mobile'),
            ('Moving Cache',	'Mobile'),
            ('MP3 (Podcache)',	'MP3'),
            ('Multi',	'Multicache'),
            ('Multicache',	'Multicache'),
            ('neznámá',	'Unknown cache'),
            ('normaler Cache',	'Traditional'),
            ('Other',	'Other'),
            ('Own cache',	'Own cache'),
            ('Podcast cache',	'Podcast cache'),
            ('Quiz',	'Quiz'),
            ('Rätselcache',	'Mystery'),
            ('Skrzynka nietypowa',	'Unusual box'),
            ('tradiční',	'Traditional'),
            ('Traditional',	'Traditional'),
            ('Traditional Cache',	'Traditional'),
            ('Tradycyjna',	'Traditional'),
            ('unbekannter Cachetyp',	'Unknown cache'),
            ('Unknown type',	'Unknown cache'),
            ('USB (Dead Drop)',	'USB'),
            ('Virtual',	'Virtual'),
            ('Virtual Cache',	'Virtual'),
            ('virtueller Cache',	'Virtual'),
            ('Webcam',	'Webcam'),
            ('Webcam Cache',	'Webcam'),
            ('Webcam-Cache',	'Webcam'),
            ('Wirtualna',	'Virtual'),
            ('Wydarzenie',	'Event');");

        $this->execute("INSERT INTO `gk-wartosci` (`name`, `value`) VALUES
            ('droga_mediana',	0),
            ('droga_srednia',	0),
            ('stat_droga',	0),
            ('stat_droga_ksiezyc',	0),
            ('stat_droga_obwod',	0),
            ('stat_droga_slonce',	0),
            ('stat_geokretow',	0),
            ('stat_geokretow_zakopanych',	0),
            ('stat_ruchow',	0),
            ('stat_userow',	0);");
        $this->execute(<<<SQL
CREATE TRIGGER `gk-geokrety_gkid` BEFORE INSERT ON `gk-geokrety` FOR EACH ROW
BEGIN
    SET NEW.gkid= COALESCE((SELECT MAX(gkid) FROM `gk-geokrety`),0) + 1;
END;
SQL);
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
        $this->execute('SET UNIQUE_CHECKS = 1;');
    }
}
