<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AwardsLegend2 extends AbstractMigration {
    public function up(): void {
        $sql = <<<EOL
UPDATE "gk_awards_group"
SET description='Ranking per most number of logs over a year', name='loggers'
WHERE name = 'movers'
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $sql = <<<EOL
UPDATE "gk_awards_group"
SET description='Ranking per most number of moves over a year', name='movers'
WHERE name = 'loggers'
EOL;
        $this->execute($sql);
    }
}
