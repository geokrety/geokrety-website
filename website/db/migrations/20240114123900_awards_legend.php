<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AwardsLegend extends AbstractMigration {
    public function up(): void {
        $sql = <<<EOL
UPDATE "gk_awards_group"
SET description='Ranking per most number of moves over a year'
WHERE name = 'movers'
EOL;
        $this->execute($sql);

        $sql = <<<EOL
DELETE FROM "gk_awards_group"
WHERE name = 'squirrels'
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $sql = <<<EOL
UPDATE "gk_awards_group"
SET description='Ranking per most number of logs over a year'
WHERE name = 'movers'
EOL;
        $this->execute($sql);

        $this->table('gk_awards_group')
            ->insert([
                ['name' => 'squirrels', 'description' => 'Ranking per most number of logs for GeoKrety never leaving users inventory over a year'],
            ]
            )->save();
    }
}
