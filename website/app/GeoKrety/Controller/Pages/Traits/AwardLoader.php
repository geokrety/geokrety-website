<?php

use GeoKrety\Model\Awards;
use GeoKrety\Service\Smarty;

trait AwardLoader {
    protected Awards $award;
    protected DB\CortexCollection $awards;

    public function loadAward(Base $f3) {
        $award = new Awards();
        $this->award = $award;
        if ($f3->exists('POST.award_id')) {
            $award->load(['id = ?', $f3->get('POST.award_id')]);
        }

        Smarty::assign('award', $this->award);
    }

    public function loadAwards(Base $f3) {
        $award = new Awards();
        $options = [
            'order' => 'id ASC',
        ];
        $this->awards = $award->find(['type = ? AND (start_on_datetime <= NOW() OR start_on_datetime = ?) AND (end_on_datetime >= NOW() OR end_on_datetime = ?)', 'manual', null, null], $options);

        Smarty::assign('awards', $this->awards);
    }
}
