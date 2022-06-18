<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MoveAudiLogsSequence extends AbstractMigration {
    public function up(): void {
        $this->execute('ALTER SEQUENCE geokrety.audit_logs_id_seq SET SCHEMA audit;');
    }

    public function down(): void {
        $this->execute('ALTER SEQUENCE audit.audit_logs_id_seq SET SCHEMA geokrety;');
    }
}
