<?php

require_once __DIR__.'/../vendor/autoload.php';

// Create GK_* consts from environments
GeoKrety\Service\Config::instance();

$db = new DB\SQL(GK_DB_DSN, GK_DB_USER, GK_DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo = $db->pdo();

return [
    'paths' => [
        'migrations' => GK_DB_MIGRATIONS_DIR,
        'seeds' => GK_DB_SEEDS_DIR,
    ],
    'foreign_keys' => true,
    'default_migration_prefix' => 'db_change_',
    'mark_generated_migration' => true,
    'migration_base_class' => Phinx\Migration\AbstractMigration::class,
    'environments' => [
        'default_environment' => 'local',
        'local' => [
            // Database name
            'name' => GK_DB_NAME,
            'connection' => $pdo,
            'schema' => GK_DB_SCHEMA,
        ],
    ],
];
