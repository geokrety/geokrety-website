<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Model\Awards;
use GeoKrety\Model\AwardsWon;
use GeoKrety\Model\YearlyRanking;
use GeoKrety\Service\DistanceFormatter;

abstract class PrizeAwarderBase {
    use Script;

    /**
     * @throws \Exception
     */
    public function process(Base $f3) {
        $this->console_writer->setPattern('Awarding badge:"%s" user:%6d:%-25s rank:%3d for %5d moves');
        $this->_process($f3);
        $this->console_writer->flush();
        $this->script_end();
    }

    protected function print(Awards $award, array $values, int $rank) {
        $this->console_writer->print([$award->name, $values['user_id'], $values['username'], $rank, $values['total']], true, true);
    }

    abstract protected function _process(Base $f3);

    protected function award(array $result, Awards $awardBase, string $description, int $year, int $rank) {
        $award = new AwardsWon();
        $award->holder = $result['user_id'];
        $award->award = $awardBase->id;
        $award->description = sprintf($description, $year, $result['total'], DistanceFormatter::format($result['distance']), $rank);
        $award->rank = $rank;
        $award->validate();
        $award->save();

        $award = new YearlyRanking();
        $award->year = $year;
        $award->user = $result['user_id'];
        $award->rank = $rank;
        $award->group = $awardBase->group->id;
        $award->distance = $result['distance'];
        $award->count = $result['total'];
        $award->award = $awardBase->id;
        $award->validate();
        $award->save();

        $this->print($awardBase, $result, $rank);
    }
}
