<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AwardsRank extends AbstractMigration {
    public function up(): void {
        $migration_year = date('Y');

        // Fix badges description
        $sql = <<<EOL
UPDATE "gk_awards"
SET name=REPLACE(name, 'mover ', 'movers ')
WHERE description LIKE '%mover %'
EOL;
        $this->execute($sql);

        // Add award group table
        $table_awards_group = $this->table('gk_awards_group', ['id' => false, 'primary_key' => 'id']);
        $table_awards_group->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('name', 'text', ['null' => false])
            ->addColumn('description', 'text', ['null' => false])
            ->create();
        $table_awards_group->insert([
                ['name' => 'movers', 'description' => 'Ranking per most number of logs over a year'],
                ['name' => 'spreaders', 'description' => 'Ranking per most number of drop logs over a year'],
                ['name' => 'squirrels', 'description' => 'Ranking per most number of logs for GeoKrety never leaving users inventory over a year'],
            ]
        )->save();

        // Add award group column
        $table_awards = $this->table('gk_awards');
        $table_awards->addColumn('group', 'biginteger', ['null' => true])
            ->addForeignKey('group', 'gk_awards_group', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->save();
        $sql = <<<'EOL'
UPDATE "gk_awards"
SET "group"=1
WHERE description LIKE '%movers %'
EOL;
        $this->execute($sql);
        $sql = <<<'EOL'
UPDATE "gk_awards"
SET "group"=2
WHERE description LIKE '%spreaders %'
EOL;
        $this->execute($sql);
        $sql = <<<'EOL'
UPDATE "gk_awards"
SET "group"=3
WHERE description LIKE '%squirrels %'
EOL;
        $this->execute($sql);

        // Create Yearly ranking table
        $table_yearly_ranking = $this->table('gk_yearly_ranking', ['id' => false, 'primary_key' => 'id']);
        $table_yearly_ranking->addColumn('id', 'biginteger', ['null' => false, 'identity' => true])
            ->addColumn('year', 'integer', ['null' => false])
            ->addColumn('user', 'biginteger', ['null' => true])
            ->addColumn('rank', 'integer', ['null' => false])
            ->addColumn('group', 'biginteger', ['null' => false])
            ->addColumn('distance', 'integer', ['null' => true])
            ->addColumn('count', 'integer', ['null' => false])
            ->addColumn('award', 'biginteger', ['null' => false])
            ->addColumn('awarded_on_datetime', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => '', 'timezone' => true])
            ->addColumn('updated_on_datetime', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'timezone' => true])
            ->addForeignKey('user', 'gk_users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addForeignKey('award', 'gk_awards_won', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('group', 'gk_awards_group', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['year'])
            ->create();

        // Defining ranks
        for ($i = 2009; $i <= $migration_year; ++$i) {
            $ids = $this->query("SELECT id FROM gk_awards WHERE name LIKE 'Top % movers $i';");
            $ids = join(',', $ids->fetchAll(PDO::FETCH_COLUMN, 0));

            if (strlen($ids)) {
                $sql = <<<EOL
WITH cte as (
     SELECT id, RANK() OVER ( ORDER BY REGEXP_REPLACE(description, '.*in [0-9]{4} \(([0-9]+), rank #.*\)', '\\1')::int DESC, REGEXP_REPLACE(description, '.*in [0-9]{4} \([0-9]+, rank #(.*)\)', '\\1')::int ASC) AS rnk
     FROM gk_awards_won
     WHERE award IN ($ids)
)

INSERT INTO gk_yearly_ranking

SELECT
nextval('gk_yearly_ranking_id_seq'),
REGEXP_REPLACE(description, '.*in ([0-9]{4}) \\(.*', '\\1')::int AS year,
holder AS "user",
cte.rnk AS rank,
(SELECT id from gk_awards_group WHERE name = 'movers') AS type,
NULL AS distance,
REGEXP_REPLACE(description, '.*in [0-9]{4} \\(([0-9]+), .*', '\\1')::int AS count,
cte.id AS award,
awarded_on_datetime,
updated_on_datetime

FROM "gk_awards_won" AS gkaw
RIGHT JOIN cte ON gkaw.id = cte.id
ORDER BY rank
LIMIT 100
EOL;
                $this->execute($sql);
            }
        }
    }

    public function down(): void {
        $table_yearly_ranking = $this->table('gk_yearly_ranking');
        $table_yearly_ranking->drop()
            ->save();

        // Add award group column
        $table_awards = $this->table('gk_awards');
        $table_awards->removeColumn('group')
            ->save();

        $table_awards_group = $this->table('gk_awards_group');
        $table_awards_group->drop()
            ->save();
    }
}
