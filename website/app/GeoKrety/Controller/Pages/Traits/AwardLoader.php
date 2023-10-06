<?php

use GeoKrety\Model\Awards;
use GeoKrety\Service\Smarty;

trait AwardLoader {
    protected Awards $award;
    protected DB\CortexCollection $awards;

    public function loadAward(Base $f3) {
        $award = new Awards();
        $this->award = $award;
        Smarty::assign('award', $this->award);

        if (empty($f3->get('POST.award_id'))) {
            return;
        }

        if (!is_numeric($f3->get('POST.award_id'))) {
            $f3->error(404, _('This award does not exist.'));
        }
        $award->load(['id = ?', $f3->get('POST.award_id')]);
        if ($award->dry()) {
            $f3->error(404, _('This award does not exist.'));
        }
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
