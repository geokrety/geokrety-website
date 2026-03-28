<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LabelLanguages extends AbstractMigration {
    public function up(): void {
        $this->table('geokrety.gk_geokrety')
            ->addColumn('label_languages', 'string', [
                'null' => true,
                'limit' => 128,
            ])
            ->update();
    }

    public function down(): void {
        $this->table('geokrety.gk_geokrety')
            ->removeColumn('label_languages')
            ->update();
    }
}
