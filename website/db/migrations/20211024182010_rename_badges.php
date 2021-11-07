<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameBadges extends AbstractMigration {
    public function up(): void {
        $table_awards = $this->table('gk_awards', ['id' => false, 'primary_key' => 'id']);
        $table_awards->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 128])
            ->addColumn('created_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addColumn('start_on_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->addColumn('end_on_datetime', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->addColumn('description', 'text', ['null' => false])
            ->addColumn('filename', 'string', ['null' => false, 'limit' => 128])
            ->create();
        $this->execute('CREATE TYPE action_type AS ENUM (\'manual\', \'automatic\')');
        $this->execute('ALTER TABLE gk_awards ADD COLUMN type action_type NOT NULL');

        $table_awards_won = $this->table('gk_badges');
        $table_awards_won->rename('gk_awards_won')
            ->addColumn('award', 'biginteger', ['null' => true])
            ->addForeignKey('award', 'gk_awards', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->save();
        $table_awards_won = $this->table('gk_awards_won');
        $this->execute('SELECT SETVAL(\'badges_id_seq\', COALESCE(MAX(id), 1) ) FROM gk_awards_won;');

        $table_awards->insert([
            ['name' => 'GK Founder', 'description' => 'GK Founder', 'filename' => 'founder.png', 'end_on_datetime' => '2013-01-12 19:02:50+00', 'type' => 'manual'],
            ['name' => 'GK Developer', 'description' => 'GK Developer', 'filename' => 'developer.png', 'type' => 'manual'],
            ['name' => 'First Donor', 'description' => 'First person who donate to GeoKrety', 'filename' => 'donor-first.svg', 'end_on_datetime' => '2018-04-17 18:48:29+00', 'type' => 'manual'],
            ['name' => 'Donor', 'description' => 'Has donated to GeoKrety', 'filename' => 'donor.svg', 'type' => 'manual'],
        ]);
        for ($i = 2009; $i <= 2020; ++$i) {
            $table_awards->insert([
                    ['name' => sprintf('Top 10 mover %d', $i), 'description' => sprintf('Top 10 mover %d', $i), 'filename' => sprintf('top10-mover-%d.png', $i), 'start_on_datetime' => sprintf('%d-01-01 00:00:00', $i), 'end_on_datetime' => sprintf('%d-01-01 00:00:00', $i + 1), 'type' => 'automatic'],
                    ['name' => sprintf('Top 100 mover %d', $i), 'description' => sprintf('Top 100 mover %d', $i), 'filename' => sprintf('top100-mover-%d.png', $i), 'start_on_datetime' => sprintf('%d-01-01 00:00:00', $i), 'end_on_datetime' => sprintf('%d-01-01 00:00:00', $i + 1), 'type' => 'automatic'],
                ]
            );
        }
        for ($i = 2021; $i <= date('Y'); ++$i) {
            $table_awards->insert([
                    ['name' => sprintf('Top 10 mover %d', $i), 'description' => sprintf('Top 10 mover %d', $i), 'filename' => sprintf('top10-mover-%d.svg', $i), 'start_on_datetime' => sprintf('%d-01-01 00:00:00', $i), 'end_on_datetime' => sprintf('%d-01-01 00:00:00', $i + 1), 'type' => 'automatic'],
                    ['name' => sprintf('Top 100 mover %d', $i), 'description' => sprintf('Top 100 mover %d', $i), 'filename' => sprintf('top100-mover-%d.svg', $i), 'start_on_datetime' => sprintf('%d-01-01 00:00:00', $i), 'end_on_datetime' => sprintf('%d-01-01 00:00:00', $i + 1), 'type' => 'automatic'],
                ]
            );
        }
        $table_awards->save();

        $this->execute('UPDATE gk_awards_won AS gkaw set award=(SELECT id FROM gk_awards WHERE filename=gkaw.filename)');
        $table_awards_won->changeColumn('award', 'biginteger', ['null' => false])
            ->removeColumn('filename')
            ->save();
    }

    public function down(): void {
        $table_awards_won = $this->table('gk_awards_won');
        $table_awards_won->addColumn('filename', 'string', ['null' => true, 'limit' => 128])
            ->save();
        $this->execute('UPDATE gk_awards_won AS gkaw set filename=(SELECT filename FROM gk_awards WHERE id=gkaw.award)');
        $table_awards_won->changeColumn('filename', 'string', ['null' => false, 'limit' => 128])
            ->save();
        $table_awards_won->rename('gk_badges')
            ->removeColumn('award')
            ->save();

        $table_awards = $this->table('gk_awards');
        $table_awards->drop()
            ->save();

        $this->execute('DROP TYPE action_type');
    }
}
