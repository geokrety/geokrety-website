<?php

namespace GeoKrety\Email;

use Exception;
use GeoKrety\Model\User;
use GeoKrety\Service\LanguageService;
use GeoKrety\Service\Smarty;
use PHPMailer\PHPMailer\PHPMailer;

abstract class BasePHPMailer extends PHPMailer {
    public array $recepients = [];

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
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function sendEmail(string $template_name): bool {
        if (sizeof($this->recepients) === 0) {
            return false;
        }
        foreach ($this->recepients as $user) {
            if (GK_DEVEL || is_null(GK_SMTP_HOST)) {
                \Base::instance()->push('SESSION.LOCAL_MAIL', [
                    'smtp' => [
                        'subject' => $this->Subject,
                        'from' => $this->From,
                        'to' => $this->to,
                    ],
                    'message' => $this->Body,
                    'read' => false,
                ]);

                return true;
            }
            $this->isHTML(true);
            Smarty::assign('user', $user);
            LanguageService::changeLanguageTo($user->preferred_language);
            $this->msgHTML(Smarty::fetch($template_name));
            parent::clearAddresses();
            parent::addAddress($user->email, $user->username);
            if (!parent::send()) {
                echo 'An error occurred while sending mail.';
            }
            LanguageService::restoreLanguageToCurrentChosen();
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public function send(): bool {
        throw new Exception('send(): Please use sendEmail() instead.');
    }

    /**
     * TODO what to do if user has no email?
     */
    protected function setTo(User $user) {
        if (!GK_IS_PRODUCTION) {
            foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
                $this->recepients[] = $user->findone(['id = ?', $admin_id]);
            }
        }
        $this->recepients[] = $user;
    }

    protected function setToAdmins() {
        $user = new User();
        foreach (GK_SITE_ADMINISTRATORS as $admin_id) {
            $this->recepients[] = $user->findone(['id = ?', $admin_id]);
        }
    }

    /**
     * @throws \Exception
     */
    public function addAddress($address, $name = '') {
        throw new Exception('addAddress(): Please use setTo() instead.');
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromSupport() {
        $this->setFrom(GK_SITE_EMAIL_SUPPORT);
    }

    protected function setSubject($subject, $prefix = GK_EMAIL_SUBJECT_PREFIX) {
        $this->Subject = $prefix.$subject;
        Smarty::assign('subject', $prefix.$subject);
    }
}
