<?php

namespace GeoKrety\Email;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\User;

abstract class Base extends \SMTP {
    public function __construct($host = GK_SMTP_HOST, $port = GK_SMTP_PORT, $scheme = GK_SMTP_SCHEME, $user = GK_SMTP_USER, $pw = GK_SMTP_PASSWORD, $ctx = null) {
        parent::__construct($host, $port, $scheme, $user, $pw, $ctx);
        $this->setFromDefault();
        $this->set('Errors-To', GK_SITE_EMAIL);
        $this->set('Content-Type', 'text/html; charset=UTF-8');
    }

    protected function setTo($userOrEmail) {
        if (is_a($userOrEmail, '\GeoKrety\Model\User')) {
            // TODO what to do if user has no email?
            $this->setToEmail($userOrEmail->email);

            return;
        }
        $this->setToEmail($userOrEmail);
    }

    private function setToEmail(string $email) {
        $this->set('To', $email);
    }

    protected function setFromDefault() {
        $this->set('From', GK_SITE_EMAIL);
    }

    protected function setFromSupport() {
        $this->set('From', GK_SITE_EMAIL_SUPPORT);
    }

    protected function setSubject($subject, $prefix = GK_EMAIL_SUBJECT_PREFIX) {
        $this->set('Subject', '=?utf-8?B?'.base64_encode($prefix.$subject).'?=');
        Smarty::assign('subject', $prefix.$subject);
    }

    public function send($message, $log = true, $mock = false) {
        if (is_null(GK_SMTP_HOST)) {
            \Base::instance()->push('SESSION.LOCAL_MAIL', array('smtp' => clone $this, 'message' => $message, 'read' => false));

            return true;
        }
        parent::send($message, $log, $mock);
    }
}
