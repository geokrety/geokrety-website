<?php

namespace GeoKrety\Email;

use Carbon\Carbon;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

abstract class TokenBase extends BasePHPMailer {
    protected string $template;
    protected array $message = [];

    public function __construct(?bool $exceptions = true, string $body = '') {
        parent::__construct($exceptions, $body);
        if (!isset($this->template)) {
            throw new \Exception('Email template undefined');
        }
    }

    /**
     * Override mail From.
     *
     * @return void
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_REGISTRATION, 'GeoKrety');
    }

    protected function messageAddLinkExpirationTime(\GeoKrety\Model\TokenBase $token): void {
        $this->message['msg'][] = sprintf(
            _('Link expires in %s.'),
            Carbon::instance($token->expire_on_datetime)->longAbsoluteDiffForHumans(['parts' => 3, 'join' => true])
        );
    }

    public function flashMessage() {
        \Flash::instance()->addMessage(join(' ', $this->message['msg']), $this->message['status']);
    }

    public function _sendActivation(User $user): \GeoKrety\Model\TokenBase {
        $token = $this->getToken($user);
        Smarty::assign('token', $token);
        $this->setTo($token->user, true);
        if ($this->sendEmail($this->template)) {
            $token->touch('last_notification_datetime');
            $token->save();
            $this->afterEmailSentHook();
            \Flash::instance()->addMessage(_('Mail sent.'), 'success');
        }

        return $token;
    }

    /**
     * @throws \Exception
     */
    abstract protected function getToken(User $user): \GeoKrety\Model\TokenBase;

    protected function afterEmailSentHook(): void {
    }

    protected function genTokenCsrf(): string {
        $f3 = \Base::instance();
        $csrf = $f3->hash($f3->SEED.
        extension_loaded('openssl') ?
            implode(unpack('L', openssl_random_pseudo_bytes(4))) :
            mt_rand()
        );

        return $csrf;
    }
}
