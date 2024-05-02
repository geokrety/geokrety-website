<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailRevalidateLastNotification extends AbstractMigration {
    public function change(): void {
        $this->table('geokrety.gk_email_revalidate')
            ->addColumn('last_notification_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->save();
    }
}
