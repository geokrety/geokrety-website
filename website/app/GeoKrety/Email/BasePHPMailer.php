<?php

namespace GeoKrety\Email;

use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Mask;
use GeoKrety\Service\Metrics;
use GeoKrety\Service\SiteSettings;
use GeoKrety\Service\Smarty;
use PHPMailer\PHPMailer\PHPMailer;
use Sugar\Event;

abstract class BasePHPMailer extends PHPMailer implements \JsonSerializable {
    public array $recipients = [];
    private array $subject = [];

    // Must be set before call to setTo() else default will apply
    protected bool $allowSend;

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
        $this->addReplyTo(GK_SITE_EMAIL_NOREPLY);
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
        $this->setFrom(GK_SITE_EMAIL, 'GeoKrety');
    }

    /**
     * @throws \Exception
     */
    public function send(): bool {
        throw new \Exception('send(): Please use sendEmail() instead.');
    }

    /**
     * @throws \Exception
     */
    public function addAddress($address, $name = '') {
        throw new \Exception('addAddress(): Please use setTo() instead.');
    }

    /**
     * Send localized messages to all users from the recipients list.
     *
     * @param string $template_name the email template to use
     *
     * @return bool True on success
     *
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \SmartyException
     */
    protected function sendEmail(string $template_name): bool {
        if (sizeof($this->recipients) === 0) {
            return false;
        }
        $return = true;
        foreach ($this->recipients as $user) {
            Metrics::counter('mail', 'Total number of sent email');
            $this->isHTML(true);
            Smarty::assign('user', $user);
            LanguageService::changeLanguageTo($user->preferred_language);
            $this->Subject = $this->getSubject();
            $this->msgHTML(Smarty::fetch($template_name));
            parent::clearAddresses();
            parent::addAddress($user->email, $user->username);
            // Save the message in local session on dev instance
            if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
                $this->store_in_local_session();
                Event::instance()->emit('mail.sent', $this);
                continue;
            }
            // When an error occured
            if (!$this->parentSend()) {
                $return = false;
                Event::instance()->emit('mail.error', $this);
                if (PHP_SAPI === 'cli') {
                    echo 'An error occurred while sending mail.';
                } else {
                    \Flash::instance()->addMessage(_('An error occurred while sending mail.'), 'danger');
                }
                continue;
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
        \Base::instance()->push('SESSION.LOCAL_MAIL', [
            'smtp' => [
                'subject' => $this->getSubject(),
                'from' => $this->From,
                'to' => $this->getToAddresses(),
            ],
            'message' => $this->Body,
            'read' => false,
        ]);
    }

    public function setTo(?User $user) {
        if (is_null($user) || !$this->allowSend($user)) {
            return;
        }
        if ($this->isProduction() || $this->allowNonProdEnvSend()) {
            $this->recipients[] = $user;
        }
        if (SiteSettings::instance()->get('ADMIN_EMAIL_BCC_ENABLED')) {
            $this->sendCopyToAdmins($user);
        }
    }

    protected function allowSend(User $user): bool {
        if (!isset($this->allowSend)) {
            $this->allowSend = $user->isEmailValid();
        }

        return $this->allowSend;
    }

    protected function allowNonProdEnvSend(): bool {
        return $this->isUnitTesting();
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
    protected function setFromNotif() {
        $this->setFrom(GK_SITE_EMAIL_NOTIF, 'GeoKrety');
    }

    public function jsonSerialize(): array {
        $to = [];
        foreach ($this->getToAddresses() as $address) {
            $to[] = [Mask::mask_email($address[0]), $address[1]];
        }

        return [
            'to' => $to,
            'subject' => $this->getSubject(),
        ];
    }

    public function sendCopyToAdmins(User $user): void {
        foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
            $admin = $this->getAdmin($admin_id);
            if (is_null($admin)) {
                continue;
            }
            $admin->username = $user->username.' (admin)';
            $admin->preferred_language = $user->preferred_language;
            $this->recipients[] = $admin;
        }
    }

    protected function getAdmin(string $admin_id): ?User {
        $admin = new User();
        $admin->load(['id = ?', $admin_id], null, 60);

        return $admin->dry() ? null : $admin;
    }

    /**
     * Extracted here so we can mock it during tests.
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function parentSend(): bool {
        return parent::send();
    }

    protected function isProduction(): bool {
        return GK_IS_PRODUCTION;
    }

    public function isUnitTesting(): bool {
        return GK_IS_UNIT_TESTING;
    }
}
