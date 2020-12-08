<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewUserEmailStatus extends AbstractMigration {
    public function up() {
        $this->execute('ALTER TABLE geokrety.gk_users DROP CONSTRAINT validate_email_invalid;');
        $this->execute('ALTER TABLE geokrety.gk_users ADD CONSTRAINT validate_email_invalid CHECK (email_invalid = ANY (ARRAY[0, 1, 2, 3]));');
    }

    public function down() {
        $this->execute('ALTER TABLE geokrety.gk_users DROP CONSTRAINT validate_email_invalid;');
        $this->execute('ALTER TABLE geokrety.gk_users ADD CONSTRAINT validate_email_invalid CHECK (email_invalid = ANY (ARRAY[0, 1, 2]));');
    }
}
