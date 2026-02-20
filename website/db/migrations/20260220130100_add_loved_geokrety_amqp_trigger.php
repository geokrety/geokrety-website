<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLovedGeokretyAmqpTrigger extends AbstractMigration {
    public function up(): void {
        // Add AMQP trigger for loved geokrety notifications
        $this->execute('
            CREATE TRIGGER after_99_notify_amqp_loves
            AFTER INSERT ON geokrety.gk_loves
            FOR EACH ROW EXECUTE FUNCTION notify_queues.amqp_notify_id();
        ');
    }

    public function down(): void {
        // Remove AMQP trigger for loved geokrety notifications
        $this->execute('
            DROP TRIGGER IF EXISTS after_99_notify_amqp_loves ON geokrety.gk_loves;
        ');
    }
}
