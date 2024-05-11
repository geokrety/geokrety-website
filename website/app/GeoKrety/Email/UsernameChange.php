<?php

namespace GeoKrety\Email;

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class UsernameChange extends BasePHPMailer {
    protected function setFromDefault() {
        $this->setFromNotif();
    }

    protected function allowNonProdEnvSend(): bool {
        return true;
    }

    public function sendUsernameChangedNotification(User $user) {
        $this->setTo($user);
        $this->setSubject(_('Your username has been changed'), '👥');
        Smarty::assign('username', $user->username);
        $this->sendEmail('emails/username-changed.tpl');
    }
}
