<?php

namespace GeoKrety\Email;

use Carbon\Carbon;
use DateTime;
use GeoKrety\Model\User;

class DailyMail extends BasePHPMailer {
    private DateTime $since;

    public function setSince(DateTime $since): void {
        $this->since = $since;
    }

    public function __construct(?bool $exceptions = true) {
        parent::__construct($exceptions);
        $this->since = new DateTime();
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendDailyMail(User $user) {
        $this->setSubject('🛩️ '.sprintf(_('Watchlist for %s'), Carbon::instance($this->since)->isoFormat('LL')));
        $this->setTo($user);
        if ($this->sendEmail('emails/daily-mail.tpl')) {
            $user->touch('last_mail_datetime');
            $user->save();
        }
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function setFromDefault() {
        $this->setFrom(GK_SITE_EMAIL_DAILY_MAIL, 'GeoKrety');
    }
}
