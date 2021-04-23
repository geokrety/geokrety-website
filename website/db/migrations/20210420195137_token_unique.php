<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TokenUnique extends AbstractMigration {
    public function up(): void {
        $this->execute('DROP INDEX "idx_21085_code";');
        $this->execute('ALTER TABLE "gk_owner_codes" ADD CONSTRAINT "idx_21085_code" UNIQUE ("token");');
    }

    public function down(): void {
        $this->execute('ALTER TABLE "gk_owner_codes" DROP CONSTRAINT "idx_21085_code";');
        $this->execute('CREATE INDEX "idx_21085_code" ON "gk_owner_codes" ("token");');
    }
}
