<?php

namespace GeoKrety\Controller\Admin;

use AwardLoader;
use GeoKrety\Controller\Base;
use GeoKrety\Model\AwardsWon;
use GeoKrety\Service\Smarty;
use UserLoader;

class UserPrizeAwarderManual extends Base {
    use AwardLoader;
    use UserLoader;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        self::loadUser($f3);
        self::loadAward($f3);
        self::loadAwards($f3);
    }

    public function get(\Base $f3) {
        Smarty::render('dialog/admin_users_prize_awarder_manual.tpl');
    }

    public function post(\Base $f3) {
        if ($this->award->dry()) {
            \Flash::instance()->addMessage(_('This award does not exists'), 'danger');
            $params = [
                'search' => $this->user->username,
            ];
            $f3->reroute(sprintf('@admin_users_list?%s', http_build_query($params)));
        }

        $award = new AwardsWon();
        $award->holder = $this->user->id;
        $award->award = $this->award->id;
        $award->description = $f3->get('POST.comment');
        $award->save();
        \Flash::instance()->addMessage(_('The prize has been awarded'), 'success');
        $f3->reroute(sprintf('@user_details(@userid=%d)#users-awards', $this->user->id));
    }
}
