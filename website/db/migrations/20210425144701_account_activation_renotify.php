<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AccountActivationRenotify extends AbstractMigration {
    public function change(): void {
        $table = $this->table('gk_account_activation');
        $table->addColumn('last_notification_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->save();
    }
}
