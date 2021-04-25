<?php

namespace GeoKrety\Email;

use DB\CortexCollection;
use GeoKrety\Model\AccountActivationToken;
use GeoKrety\Service\Smarty;

class ExpiringAccounts extends BasePHPMailer {
    public function sendExpiredAccountToAdmins(CortexCollection $expiringTokens) {
        $this->setToAdmins();
        $this->setFrom(GK_SITE_EMAIL_ADMIN, 'GeoKrety');
        Smarty::assign('expiringTokens', $expiringTokens);
        Smarty::assign('totalExpiringTokens', sizeof($expiringTokens));
        $this->setSubject('Some accounts will be deleted', '❗');
        $this->sendEmail('emails/expiring-accounts.tpl');
    }

    public function sendAboutToExpireAgain(AccountActivationToken $token) {
        $token->touch('last_notification_datetime');
        $token->save();
        $this->setTo($token->user, true);
        $this->setFrom(GK_SITE_EMAIL_REGISTRATION, 'GeoKrety');
        Smarty::assign('token', $token);
        $this->setSubject('Account not yet active', '❗');
        $this->sendEmail('emails/expiring-accounts-renotify.tpl');
    }

    public function sendAccountDeletionInfo(AccountActivationToken $token) {
        $this->setTo($token->user, true);
        $this->setFrom(GK_SITE_EMAIL_REGISTRATION, 'GeoKrety');
        Smarty::assign('token', $token);
        $this->setSubject('Account has been permanently deleted', '🥺');
        $this->sendEmail('emails/expiring-accounts-deleted.tpl');
    }
}
