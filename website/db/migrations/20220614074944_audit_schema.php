<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuditSchema extends AbstractMigration {
    public function up(): void {
        $this->execute('CREATE SCHEMA "audit"');
        $this->execute('ALTER TABLE gk_audit_logs SET SCHEMA "audit";');
        $this->execute('ALTER TABLE audit.gk_audit_logs RENAME TO "actions_logs";');
    }

    public function down(): void {
        $this->execute('DROP SCHEMA "audit"');
        $this->execute('ALTER TABLE audit.actions_logs SET SCHEMA "geokrety";');
        $this->execute('ALTER TABLE actions_logs RENAME TO "gk_audit_logs";');
    }
}
