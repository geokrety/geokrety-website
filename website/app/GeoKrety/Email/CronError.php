<?php

namespace GeoKrety\Email;

use GeoKrety\Model\Scripts;
use GeoKrety\Service\Smarty;

class CronError extends BasePHPMailer {
    public function __construct(?bool $exceptions = true, string $body = '') {
        parent::__construct($exceptions, $body);
        $this->setToAdmins();
    }

    public function sendPartnerFailure(array $errors) {
        Smarty::assign('errors', $errors);
        $this->setSubject(sprintf(_('Cron Failure: %s'), join(', ', array_keys($errors))), '❗');
        $this->sendEmail('emails/cron-partner-failure.tpl');
    }

    public function sendException(string $cron_name, array $errors) {
        Smarty::assign('cron', $cron_name);
        Smarty::assign('errors', $errors);
        $this->setSubject(sprintf(_('Cron Exception: %s'), $cron_name), '❗');
        $this->sendEmail('emails/cron-exception.tpl');
    }

    public function sendLockedScript(Scripts $script) {
        Smarty::assign('script', $script);
        $this->setSubject(sprintf(_('Cron is locked since a long time: %s'), $script->name), '❗');
        $this->sendEmail('emails/cron-locked.tpl');
    }

    public function sendPartnerFatal(?string $service, string $error) {
        Smarty::assign('service', $service);
        Smarty::assign('error', $error);
        $this->setSubject(sprintf(_('Cron fatal error: %s'), $service), '❗');
        $this->sendEmail('emails/cron-partner-fatal.tpl');
    }

    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_ADMIN, 'GeoKrety');
    }
}
