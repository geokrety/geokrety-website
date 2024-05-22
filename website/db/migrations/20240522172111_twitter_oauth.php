<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TwitterOauth extends AbstractMigration {
    public function up(): void {
        $table_providers = $this->table('gk_social_auth_providers');
        $table_providers->insert([
            ['name' => 'Twitter'],
        ])->save();
    }

    public function down(): void {
        $this->execute(<<<'EOL'
DELETE FROM geokrety.gk_social_auth_providers
WHERE name = 'Twitter';
EOL);
    }
}
