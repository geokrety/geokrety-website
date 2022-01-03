<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\AwardsGroup;
use GeoKrety\Model\YearlyRanking;
use GeoKrety\Service\Smarty;

class StatisticsYearlyRanking extends Base {
    public function index(\Base $f3) {
        $awardsGroup = new AwardsGroup();
        $awardsGroups = $awardsGroup->find(null, ['order' => 'name ASC']);
        Smarty::assign('awards_groups', $awardsGroups);

        Smarty::render('pages/statistics_awards_ranking_index.tpl');
    }

    public function ranking(\Base $f3) {
        $awardGroup = new AwardsGroup();
        $awardGroup->load(['name = ?', $f3->get('PARAMS.award')]);
        if ($awardGroup->dry()) {
            $f3->error(404, _('This ranking does not exists.'));
        }
        Smarty::assign('award_group', $awardGroup);

        $yearlyRanking = new YearlyRanking();
        $yearlyRanking->has('group', ['name = ?', $f3->get('PARAMS.award')]);
        $awards = $yearlyRanking->find(['rank <= 100'], ['order' => 'rank ASC, year DESC']);
        Smarty::assign('awards', $awards);

        Smarty::render('pages/statistics_awards_ranking.tpl');
    }
}
