<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameBadges2023 extends AbstractMigration {
    public function up(): void {
        $start = '2023-01-01 00:00:00+00';
        $end = '2024-01-01 00:00:00+00';
        $this->execute("DELETE FROM geokrety.gk_awards where name like 'Top % movers 2023'");

        $this->table('geokrety.gk_awards')
            ->insert([
                    ['name' => 'Top 10 loggers 2023', 'description' => 'Top 10 loggers 2023', 'filename' => 'top10-loggers-2023.svg', 'start_on_datetime' => $start, 'end_on_datetime' => $end, 'type' => 'automatic', 'group' => 1],
                    ['name' => 'Top 100 loggers 2023', 'description' => 'Top 100 loggers 2023', 'filename' => 'top100-loggers-2023.svg', 'start_on_datetime' => $start, 'end_on_datetime' => $end, 'type' => 'automatic', 'group' => 1],
                ]
            )->save();
    }

    public function down(): void {
        $start = '2023-01-01 00:00:00+00';
        $end = '2024-01-01 00:00:00+00';
        $this->execute("DELETE FROM geokrety.gk_awards where name like 'Top % loggers 2023'");

        $this->table('geokrety.gk_awards')
            ->insert([
                    ['name' => 'Top 10 movers 2023', 'description' => 'Top 10 movers 2023', 'filename' => 'top10-mover-2023.svg', 'start_on_datetime' => $start, 'end_on_datetime' => $end, 'type' => 'automatic', 'group' => 1],
                    ['name' => 'Top 100 movers 2023', 'description' => 'Top 100 movers 2023', 'filename' => 'top100-mover-2023.svg', 'start_on_datetime' => $start, 'end_on_datetime' => $end, 'type' => 'automatic', 'group' => 1],
                ]
            )->save();
    }
}
