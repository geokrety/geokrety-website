<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AccountStatus extends AbstractMigration {
    public function up() {
        $this->execute('ALTER TABLE geokrety.gk_users DROP CONSTRAINT validate_account_valid;');
        $this->execute('ALTER TABLE geokrety.gk_users ADD CONSTRAINT validate_account_valid CHECK (account_valid = ANY (ARRAY[0, 1, 2]));');
    }

    public function down() {
        $this->execute('ALTER TABLE geokrety.gk_users DROP CONSTRAINT validate_account_valid;');
        $this->execute('ALTER TABLE geokrety.gk_users ADD CONSTRAINT validate_account_valid CHECK (account_valid = ANY (ARRAY[0, 1]));');
    }
}
