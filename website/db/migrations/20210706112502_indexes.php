<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Indexes extends AbstractMigration {
    public function change(): void {
        $table = $this->table('gk_geokrety');
        $table->addIndex(['created_on_datetime'])
            ->save();
    }
}
