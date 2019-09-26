<?php

chdir('website');

// Framework bootstrap code here
require 'init-f3.php';

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
        'default_database' => 'local',
        'local' => [
            // Database name
            'name' => $pdo->query('select database()')->fetchColumn(),
            'connection' => $pdo,
        ],
    ],
];
