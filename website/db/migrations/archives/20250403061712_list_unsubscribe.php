<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ListUnsubscribe extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
ALTER TABLE IF EXISTS geokrety.gk_users
    ADD COLUMN list_unsubscribe_token uuid NOT NULL DEFAULT gen_random_uuid();
EOL
        );
    }

    public function down(): void {
        $this->execute(<<<'EOL'
ALTER TABLE geokrety.gk_users
    DROP COLUMN list_unsubscribe_token;
EOL
        );
    }
}
