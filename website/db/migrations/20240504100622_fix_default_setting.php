<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixDefaultSetting extends AbstractMigration {
    public function change(): void {
        $this->execute(<<<'EOL'
UPDATE geokrety.gk_users_settings_parameters
SET "default"=false
WHERE name = 'DISPLAY_ABSOLUTE_DATE';
EOL);
    }
}
