<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Scripts;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class CronError extends Base {
    public function sendPartnerFailure(array $errors) {
        Smarty::assign('errors', $errors);
        $this->setSubject(sprintf('❗ Cron Failure: %s', join(', ', array_keys($errors))));
        $this->sendEmail('emails/cron-partner-failure.tpl');
    }

    protected function sendEmail(string $template_name) {
        $user = new User();
        Smarty::assign('user', $user);
        foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
            $user->load(['id = ?', $admin_id]);
            $this->setTo($user);
            $this->send(Smarty::fetch($template_name));
        }
    }

    public function sendException(string $cron_name, array $errors) {
        Smarty::assign('cron', $cron_name);
        Smarty::assign('errors', $errors);
        $this->setSubject(sprintf('❗ Cron Exception: %s', $cron_name));
        $this->sendEmail('emails/cron-exception.tpl');
    }

    public function sendLockedScript(Scripts $script) {
        Smarty::assign('script', $script);
        $this->setSubject(sprintf('❗ Cron is locked since a long time: %s', $script->name));
        $this->sendEmail('emails/cron-locked.tpl');
    }

    public function sendPartnerFatal(?string $service, string $error) {
        Smarty::assign('service', $service);
        Smarty::assign('error', $error);
        $this->setSubject(sprintf('❗ Cron fatal error: %s', $service));
        $this->sendEmail('emails/cron-partner-fatal.tpl');
    }

    protected function setFromDefault() {
        $this->set('From', GK_SITE_EMAIL_ADMIN);
    }
}
