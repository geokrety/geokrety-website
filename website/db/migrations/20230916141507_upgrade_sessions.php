<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpgradeSessions extends AbstractMigration {
    public function up(): void {
        $GK_SITE_SESSION_REMEMBER = GK_SITE_SESSION_REMEMBER;
        $sql = <<<EOL
UPDATE "sessions"
SET stamp = stamp + {$GK_SITE_SESSION_REMEMBER}
WHERE persistent = FALSE;
EOL;
        $this->execute($sql);

        $GK_SITE_SESSION_LIFETIME_REMEMBER = GK_SITE_SESSION_LIFETIME_REMEMBER;
        $sql = <<<EOL
UPDATE "sessions"
SET stamp = stamp + {$GK_SITE_SESSION_LIFETIME_REMEMBER}
WHERE persistent = TRUE;
EOL;
        $this->execute($sql);
    }

    public function down(): void {
        $GK_SITE_SESSION_REMEMBER = GK_SITE_SESSION_REMEMBER;
        $sql = <<<EOL
UPDATE "sessions"
SET stamp = stamp - {$GK_SITE_SESSION_REMEMBER}
WHERE persistent = FALSE;
EOL;
        $this->execute($sql);

        $GK_SITE_SESSION_LIFETIME_REMEMBER = GK_SITE_SESSION_LIFETIME_REMEMBER;
        $sql = <<<EOL
UPDATE "sessions"
SET stamp = stamp - {$GK_SITE_SESSION_LIFETIME_REMEMBER}
WHERE persistent = TRUE;
EOL;
        $this->execute($sql);
    }
}
