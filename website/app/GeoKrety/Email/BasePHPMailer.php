<?php

namespace GeoKrety\Email;

use Base;
use Exception;
use Flash;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Metrics;
use GeoKrety\Service\Smarty;
use PHPMailer\PHPMailer\PHPMailer;

abstract class BasePHPMailer extends PHPMailer {
    public array $recipients = [];
    private array $subject = [];

    /**
     * myPHPMailer constructor.
     *
     * @param string $body A default HTML message body
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function __construct(?bool $exceptions = true, string $body = '') {
        parent::__construct($exceptions);
        LanguageService::changeLanguageTo('en'); // So all string are prepared in English
        $this->setFromDefault();
        $this->addCustomHeader('Errors-To', GK_SITE_EMAIL);
        $this->addCustomHeader('X-GKPHPMailer', 'true');
        $this->isSMTP();
        $this->Host = GK_SMTP_URI;
        $this->SMTPAuth = (bool) GK_SMTP_USER;
        $this->Username = GK_SMTP_USER;
        $this->Password = GK_SMTP_PASSWORD;
        //$this->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->CharSet = self::CHARSET_UTF8;
        $this->Body = $body;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL, 'Geokrety');
    }

    /**
     * @throws \Exception
     */
    public function send(): bool {
        throw new Exception('send(): Please use sendEmail() instead.');
    }

    /**
     * @throws \Exception
     */
    public function addAddress($address, $name = '') {
        throw new Exception('addAddress(): Please use setTo() instead.');
    }

    protected function sendEmail(string $template_name): bool {
        if (sizeof($this->recipients) === 0) {
            return false;
        }
        foreach ($this->recipients as $user) {
            Metrics::counter('mail', 'Total number of sent email');
            $this->isHTML(true);
            Smarty::assign('user', $user);
            LanguageService::changeLanguageTo($user->preferred_language);
            $this->Subject = $this->getSubject();
            $this->msgHTML(Smarty::fetch($template_name));
            parent::clearAddresses();
            parent::addAddress($user->email, $user->username);
            if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
                $this->store_in_local_session();
                continue;
            }
            if (!parent::send()) {
                if (PHP_SAPI === 'cli') {
                    echo 'An error occurred while sending mail.';
                } else {
                    Flash::instance()->addMessage(_('An error occurred while sending mail.'), 'danger');
                }
            }
        }
        $this->recipients = [];
        LanguageService::restoreLanguageToCurrentChosen();

        return true;
    }

    protected function getSubject(): string {
        $values = $this->subject;

        return sprintf('%s %s %s', $values[0], $values[1], _($values[2]));
    }

    protected function setSubject(string $subject, string $emoji = '', string $prefix = GK_EMAIL_SUBJECT_PREFIX) {
        $this->subject = [$prefix, $emoji, $subject];
    }

    private function store_in_local_session() {
        Base::instance()->push('SESSION.LOCAL_MAIL', [
            'smtp' => [
                'subject' => $this->getSubject(),
                'from' => $this->From,
                'to' => $this->getToAddresses(),
            ],
            'message' => $this->Body,
            'read' => false,
        ]);
    }

    /**
     * @param bool $force         Force sending the mail, by user email validity check useful on registration
     * @param bool $realRecipient Deliver the mail to the real address. Useful to prevent crons to send unsolicited mails, but allow users to tests features on staging. (Only relevant when not production
     */
    protected function setTo(User $user, bool $force = false, bool $realRecipient = true) {
        if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
            $this->recipients[] = $user;

            return;
        }
        if (!GK_IS_PRODUCTION) {
            foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
                $_admin = $user->findone(['id = ?', $admin_id]);
                $_user = clone $user;
                $_user->email = $_admin->email;
                $_user->username .= ' (admin)';
                $this->recipients[] = $_user;
            }
        }
        if (GK_IS_PRODUCTION or $realRecipient) {
            if (!$user->hasEmail() or (!$user->isEmailValid() and !$force)) {
                return;
            }
            $this->recipients[] = $user;
        }
    }

    protected function setToAdmins() {
        $user = new User();
        foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
            $this->recipients[] = $user->findone(['id = ?', $admin_id]);
        }
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromSupport() {
        $this->setFrom(GK_SITE_EMAIL_SUPPORT, 'GeoKrety');
    }
}
