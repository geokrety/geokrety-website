<?php

namespace GeoKrety\Email;

use Carbon\Carbon;
use GeoKrety\Model\AccountActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class AccountActivation extends TokenBase {
    protected string $template = 'emails/account-activation.tpl';
    public const SESSION_SEND_ACTIVATION_AGAIN = 'SESSION.sendActivationAgainOnLoginCSRF';

    /**
     * @param bool|null $exceptions
     * @param string $body
     */
    public function __construct(?bool $exceptions = true, string $body = '') {
        parent::__construct($exceptions, $body);
        parent::setSubject(_('Welcome to GeoKrety.org'), '🎉');
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

    /**
     * When the account has just been created.
     *
     * @return void
     */
    public function sendActivationOnCreate(User $user) {
        $this->message['status'] = 'warning';
        $this->message['msg'][] = _('A confirmation email has been sent to your address.');
        $this->message['msg'][] = sprintf('<strong>%s</strong>', _('You must click on the link provided in the email to activate your account before your can use it.'));
        $token = $this->_sendActivation($user);
        $this->messageAddLinkExpirationTime($token);
        $this->flashMessage($token);
    }

    /**
     * When the user try to create the same account and the account wasn't activated yet.
     *
     * @return void
     */
    public function sendActivationOnCreateAgain(AccountActivationToken $token) {
        die(); // TODO No unit test for this?
        $this->message['status'] = 'danger';
        $this->message['msg'][] = _('Your account seems to already exist.');
        $this->message['msg'][] = _('The confirmation email was sent again to your mail address.');
        $this->message['msg'][] = sprintf('<strong>%s</strong>', _('You must click on the link provided in the email to activate your account before your can use it.'));
        $this->messageAddLinkExpirationTime($token);
        $this->flashMessage($token);
        // $this->_sendActivation($token);
    }

    /**
     * When user try to login, but the account has not yet been activated.
     *
     * @return void
     */
    public function sendActivationAgainOnLogin(User $user) {
        $token = $this->getToken($user);

        $f3 = \Base::instance();
        $csrf = $this->genTokenCsrf();
        $f3->set(self::SESSION_SEND_ACTIVATION_AGAIN, $csrf);

        $this->message['status'] = 'danger';
        $this->message['msg'][] = sprintf('<strong>%s</strong>', _('Your account is not yet active.'));
        $this->message['msg'][] = sprintf(
            _('You can request a <a href="%s">new confirmation mail</a>.'),
            $f3->alias('user_account_revalidation_send_mail', [
                'tokenid' => $token->id,
                'csrf' => $csrf,
            ])
        );
        $this->message['msg'][] = sprintf('<strong>%s</strong>', _('You must click on the link provided in the email to activate your account before your can use it.'));
        // $this->messageAddLinkExpirationTime($token);
        $this->flashMessage($token);
        // $this->_sendActivation($token);
    }

    /**
     * When the account has been activated.
     *
     * @return void
     *
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \SmartyException
     */
    public function sendActivationConfirm(AccountActivationToken $token) {
        Smarty::assign('token', $token);
        $this->setSubject(_('Account activated'), '🎉');
        $this->setTo($token->user);
        $this->sendEmail('emails/account-activated.tpl');
    }

    protected function getToken(User $user): \GeoKrety\Model\TokenBase {
        $token = new AccountActivationToken();
        $token->loadUserActiveToken($user);
        if ($token->dry()) {
            $token->user = $user;
            $token->save();
        }
        return $token;
    }

    /**
     * @return void
     */
    protected function afterEmailSentHook(): void {
        \Base::instance()->clear(self::SESSION_SEND_ACTIVATION_AGAIN);
    }
}
