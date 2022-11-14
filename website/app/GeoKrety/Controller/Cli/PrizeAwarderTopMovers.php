<?php

namespace GeoKrety\Controller\Cli;

use Base;
use Exception;
use GeoKrety\LogType;
use GeoKrety\Model\Awards;
use GeoKrety\Model\AwardsWon;

class PrizeAwarderTopMovers extends PrizeAwarderBase {
    /**
     * @throws \Exception
     */
    protected function _process(Base $f3) {
        $this->topMovers($f3);
    }

    /**
     * @throws \Exception
     */
    private function topMovers(Base $f3) {
        $this->script_start(__METHOD__);
        $year = $f3->get('PARAMS.year');
        $sql = <<<'EOT'
            SELECT gkm.author AS user_id, gku.username AS username, count(*) as total, SUM(distance) AS distance
            FROM gk_moves AS gkm
            LEFT JOIN gk_users AS gku ON gkm.author = gku.id
            WHERE date_part('year', created_on_datetime) = ?
            AND author IS NOT NULL
            AND move_type = ?
            GROUP BY gkm.author, gku.username
            ORDER BY total DESC, SUM(distance) DESC
            LIMIT 100
EOT;
        $result = $f3->get('DB')->exec($sql, [$year, LogType::LOG_TYPES_ALIVE]);

        $award_top10 = new Awards();
        $award_top10->load(['name = ?', sprintf('Top 10 movers %d', $year)]);
        if ($award_top10->dry()) {
            throw new Exception(sprintf('"Top 10 movers %d" award does not exist', $year));
        }
        $this->check_overdue($award_top10, $year);

        $award_top100 = new Awards();
        $award_top100->load(['name = ?', sprintf('Top 100 movers %d', $year)]);
        if ($award_top100->dry()) {
            throw new Exception(sprintf('"Top 100 movers %d" award does not exist', $year));
        }

        $award_top10_size = sizeof($result) > 10 ? 10 : sizeof($result);
        $award_top100_size = sizeof($result);

        // Awarding first 10
        for ($i = 0; $i < $award_top10_size; ++$i) {
            $this->award(
                $result[$i],
                $award_top10,
                'Top 10 movers in %d (total %d drops, %s, rank #%d)',
                $year,
                $i + 1,
            );
        }

        // Awarding next 11-100
        for ($i = 10; $i < $award_top100_size; ++$i) {
            $this->award(
                $result[$i],
                $award_top10,
                'Top 100 movers in %d (total %d drops, %s, rank #%d)',
                $year,
                $i + 1,
            );
        }
    }

    protected function _pre_check(Base $f3) {
        $year = $f3->get('PARAMS.year');
        $award = new AwardsWon();
        $award->has('award', ['name = ?', sprintf('Top 10 movers %d', $year)]);
        $award->load();
        if (!$award->dry()) {
            echo $this->console_writer->sprintf("\e[0;31mAward '%s' already exists for year %d\e[0m", 'movers', $year).PHP_EOL;
            exit(1);
        }
    }
}
