<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RankingUniq extends AbstractMigration {
    public function up(): void {
        $sql = <<<'EOL'
ALTER TABLE ONLY gk_yearly_ranking
ADD CONSTRAINT gk_yearly_ranking_uniq UNIQUE ("year", "user", "award")
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $sql = <<<'EOL'
ALTER TABLE ONLY gk_yearly_ranking
DROP CONSTRAINT gk_yearly_ranking_uniq
EOL;
        $this->execute($sql);
    }
}
