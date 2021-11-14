<?php

namespace GeoKrety\Email;

use GeoKrety\Model\AwardsWon;
use GeoKrety\Service\Smarty;

class Awards extends BasePHPMailer {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendAwardReceived(AwardsWon $award) {
        Smarty::assign('award', $award);
        $this->setTo($award->holder, false, false);
        $this->setSubject(_('You have received an Award'), '🏆');
        $this->sendEmail('emails/award-awarded.tpl');
    }
}
