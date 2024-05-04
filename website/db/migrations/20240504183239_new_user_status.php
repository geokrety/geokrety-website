<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewuserStatus extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'EOL'
ALTER TABLE geokrety.gk_users DROP CONSTRAINT validate_email_invalid;
ALTER TABLE geokrety.gk_users ADD CONSTRAINT validate_email_invalid CHECK (email_invalid = ANY (ARRAY[0, 1, 2, 3, 4]));
EOL);
    }

    public function down(): void {
        $this->execute(<<<'EOL'
ALTER TABLE geokrety.gk_users DROP CONSTRAINT validate_email_invalid;
ALTER TABLE geokrety.gk_users ADD CONSTRAINT validate_email_invalid CHECK (email_invalid = ANY (ARRAY[0, 1, 2, 3]));
EOL);
    }
}
