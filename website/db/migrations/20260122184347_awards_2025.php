<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Awards2025 extends AbstractMigration {
    public function up(): void {
        $table = $this->table('gk_awards_group');
        // TODO Insert groups if they do not already exist
        if (!$this->fetchRow("SELECT id FROM gk_awards_group WHERE name = 'loggers' LIMIT 1")) {
            $table->insert([
                [
                    'id' => 1,
                    'name' => 'loggers',
                    'description' => 'Ranking per most number of drop logs over a year',
                ],
            ])->saveData();
        }
        if (!$this->fetchRow("SELECT id FROM gk_awards_group WHERE name = 'spreaders' LIMIT 1")) {
            $table->insert([
                [
                    'id' => 2,
                    'name' => 'spreaders',
                    'description' => 'Ranking per most number of logs over a year',
                ],
            ])->saveData();
        }
        $table = $this->table('gk_awards');
        $table->insert([
            [
                'name' => 'Top 10 loggers 2025',
                'start_on_datetime' => '2025-01-01 00:00:00+00',
                'end_on_datetime' => '2025-12-31 23:59:59+00',
                'description' => 'Top 10 loggers 2025',
                'filename' => 'top10-loggers-2025.svg',
                'type' => 'automatic',
                'group' => 1,
            ],
            [
                'name' => 'Top 100 loggers 2025',
                'start_on_datetime' => '2025-01-01 00:00:00+00',
                'end_on_datetime' => '2025-12-31 23:59:59+00',
                'description' => 'Top 100 loggers 2025',
                'filename' => 'top100-loggers-2025.svg',
                'type' => 'automatic',
                'group' => 1,
            ],
            [
                'name' => 'Top 10 spreaders 2025',
                'start_on_datetime' => '2025-01-01 00:00:00+00',
                'end_on_datetime' => '2025-12-31 23:59:59+00',
                'description' => 'Top 10 spreaders 2025',
                'filename' => 'top10-spreaders-2025.svg',
                'type' => 'automatic',
                'group' => 2,
            ],
            [
                'name' => 'Top 100 spreaders 2025',
                'start_on_datetime' => '2025-01-01 00:00:00+00',
                'end_on_datetime' => '2025-12-31 23:59:59+00',
                'description' => 'Top 100 spreaders 2025',
                'filename' => 'top100-spreaders-2025.svg',
                'type' => 'automatic',
                'group' => 2,
            ],
        ])->saveData();
    }

    public function down(): void {
        $this->execute("DELETE FROM gk_awards WHERE name IN (
            'Top 10 loggers 2025',
            'Top 100 loggers 2025',
            'Top 10 spreaders 2025',
            'Top 100 spreaders 2025'
        )");
    }
}
