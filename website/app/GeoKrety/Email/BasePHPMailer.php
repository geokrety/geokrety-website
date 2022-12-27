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
use Sugar\Event;

abstract class BasePHPMailer extends PHPMailer implements \JsonSerializable {
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
        $this->setFromDefault();
        $this->addCustomHeader('Errors-To', GK_SITE_EMAIL);
        $this->addCustomHeader('X-GKPHPMailer', 'true');
        $this->isSMTP();
        $this->Host = GK_SMTP_URI;
        $this->SMTPAuth = (bool) GK_SMTP_USER;
        $this->Username = GK_SMTP_USER;
        $this->Password = GK_SMTP_PASSWORD;
        // $this->SMTPDebug = SMTP::DEBUG_SERVER;
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
        $return = true;
        foreach ($this->recipients as $user) {
            Metrics::counter('mail', 'Total number of sent email');
            $this->isHTML(true);
            $this->addReplyTo(GK_SITE_EMAIL_NOREPLY);
            Smarty::assign('user', $user);
            LanguageService::changeLanguageTo($user->preferred_language);
            $this->Subject = $this->getSubject();
            $this->msgHTML(Smarty::fetch($template_name));
            parent::clearAddresses();
            parent::addAddress($user->email, $user->username);
            if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
                $this->store_in_local_session();
                Event::instance()->emit('mail.sent', $this);
                continue;
            }
            if (!parent::send()) {
                $return = false;
                Event::instance()->emit('mail.error', $this);
                if (PHP_SAPI === 'cli') {
                    echo 'An error occurred while sending mail.';
                } else {
                    Flash::instance()->addMessage(_('An error occurred while sending mail.'), 'danger');
                }
            }
            Event::instance()->emit('mail.sent', $this);
        }
        $this->recipients = [];
        LanguageService::restoreLanguageToCurrentChosen();

        return $return;
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
     * @param bool $force         Force sending the mail, bypass user email validity check, useful on registration
     * @param bool $realRecipient Deliver the mail to the real address. Useful to prevent crons to send unsolicited
     *                            mails, but allow users to tests features on staging.
     *                            (Only relevant when not production)
     */
    protected function setTo(?User $user, bool $force = false, bool $realRecipient = true) {
        if (is_null($user)) {
            return;
        }
        if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
            $this->recipients[] = $user;

            return;
        }
        if (!GK_IS_PRODUCTION) {
            foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
                $_admin = $user->findone(['id = ?', $admin_id], null, 60);
                $_user = clone $user;
                $_user->email = $_admin->email;
                $_user->username .= ' (admin)';
                $this->recipients[] = $_user;
            }
        }
        if ($realRecipient) {
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

    public function jsonSerialize(): array {
        $to = [];
        foreach ($this->getToAddresses() as $address) {
            $to[] = [mask_email($address[0]), $address[1]];
        }

        return [
            'to' => $to,
            'subject' => $this->getSubject(),
        ];
    }
}

// Function from: https://stackoverflow.com/a/45944844/944936
function mask($str, $first, $last) {
    $len = strlen($str);
    $toShow = $first + $last;

    return substr($str, 0, $len <= $toShow ? 0 : $first).str_repeat('*', $len - ($len <= $toShow ? 0 : $toShow)).substr($str, $len - $last, $len <= $toShow ? 0 : $last);
}
// Function from: https://stackoverflow.com/a/45944844/944936
function mask_email($email) {
    $mail_parts = explode('@', $email);
    $domain_parts = explode('.', $mail_parts[1]);

    $mail_parts[0] = mask($mail_parts[0], 2, 1); // show first 2 letters and last 1 letter
    $domain_parts[0] = mask($domain_parts[0], 2, 1); // same here
    $mail_parts[1] = implode('.', $domain_parts);

    return implode('@', $mail_parts);
}
