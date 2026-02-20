<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GeokretLoves extends AbstractMigration {
    public function up(): void {
        // Create the loves table to track which user loved which GeoKret
        $table = $this->table('geokrety.gk_loves', ['id' => true, 'primary_key' => ['id']]);
        $table->addColumn('user', 'integer', ['null' => false])
            ->addColumn('geokret', 'integer', ['null' => false])
            ->addColumn('created_on_datetime', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'timezone' => true,
            ])
            ->addIndex(['user', 'geokret'], ['unique' => true, 'name' => 'idx_gk_loves_user_geokret'])
            ->addIndex(['geokret'], ['name' => 'idx_gk_loves_geokret'])
            ->addForeignKey('user', 'geokrety.gk_users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('geokret', 'geokrety.gk_geokrety', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        // Add loves_count counter column to gk_geokrety
        $table_geokrety = $this->table('geokrety.gk_geokrety');
        $table_geokrety->addColumn('loves_count', 'integer', [
            'default' => 0,
            'null' => false,
        ])
            ->update();

        // Create trigger function to maintain loves_count
        $this->execute(<<<'EOL'
CREATE OR REPLACE FUNCTION geokrety.gk_loves_update_count()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    VOLATILE
    COST 100
AS $BODY$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE geokrety.gk_geokrety
        SET loves_count = loves_count + 1
        WHERE id = NEW.geokret;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE geokrety.gk_geokrety
        SET loves_count = GREATEST(0, loves_count - 1)
        WHERE id = OLD.geokret;
    END IF;
    RETURN NULL;
END;
$BODY$;
EOL);

        // Create trigger on gk_loves table
        $this->execute(<<<'EOL'
CREATE TRIGGER after_gk_loves_update_count
    AFTER INSERT OR DELETE
    ON geokrety.gk_loves
    FOR EACH ROW
    EXECUTE FUNCTION geokrety.gk_loves_update_count();
EOL);

        // Recalculate existing count (for future-proofing, table is new so it's 0)
        $this->execute(<<<'EOL'
UPDATE geokrety.gk_geokrety g
SET loves_count = (
    SELECT COUNT(*) FROM geokrety.gk_loves l WHERE l.geokret = g.id
);
EOL);
    }

    public function down(): void {
        $this->execute('DROP TRIGGER IF EXISTS after_gk_loves_update_count ON geokrety.gk_loves');
        $this->execute('DROP FUNCTION IF EXISTS geokrety.gk_loves_update_count()');

        $this->table('geokrety.gk_geokrety')
            ->removeColumn('loves_count')
            ->update();

        $this->table('geokrety.gk_loves')->drop()->save();
    }
}
