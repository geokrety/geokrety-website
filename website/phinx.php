<?php

// Framework bootstrap code here
require __DIR__.'/init-f3.php';

// Get PDO object
$pdo = $f3->get('DB')->pdo();

return [
    'paths' => [
        'migrations' => GK_DB_MIGRATIONS_DIR,
        'seeds' => GK_DB_SEEDS_DIR,
    ],
    'foreign_keys' => true,
    'default_migration_prefix' => 'db_change_',
    'mark_generated_migration' => true,
    'migration_base_class' => \Phinx\Migration\AbstractMigration::class,
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
