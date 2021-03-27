<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Scripts;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class CronError extends Base {
    public function sendError(User $user, string $cron, array $error) {
        Smarty::assign('user', $user);
        Smarty::assign('cron', $cron);
        Smarty::assign('error', $error);
        $this->setSubject('❗ '.sprintf(_('Cron Failure: %s'), $cron));
        $message = Smarty::fetch('cron-failure.html');
        $this->sendEmail($user, $message);
    }

    public function sendLockedScript(User $user, Scripts $script) {
        Smarty::assign('user', $user);
        Smarty::assign('script', $script);
        $this->setSubject('❗ '.sprintf(_('Cron is locked since a long time: %s'), $script->name));
        $message = Smarty::fetch('cron-locked.html');
        $this->sendEmail($user, $message);
    }

    protected function sendEmail(User $user, string $message) {
        $this->setTo($user);
        if (!$this->send($message)) {
            \Flash::instance()->addMessage(_('An error occurred while sending the cron mail.'), 'danger');
        }
    }

    protected function setFromDefault() {
        $this->set('From', GK_SITE_EMAIL_ADMIN);
    }
}
