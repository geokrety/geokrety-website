<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Email\ExpiringAccounts;
use GeoKrety\Model\AccountActivationToken as AccountActivationTokenModel;

class AccountActivationToken {
    use Script;

    public function deleteNeverActivatedAccounts() {
        $this->script_start(__METHOD__);
        $this->console_writer->print(['Finding expiring accounts']);
        $activation = new AccountActivationTokenModel();
        $expiringTokens = $activation->find([
            'used = ? AND created_on_datetime < NOW() - cast(? as interval)',
            AccountActivationTokenModel::TOKEN_UNUSED,
            GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY.' DAY',
        ]);
        if ($expiringTokens === false) {
            $this->console_writer->print(['No account to remove'], true);
            $this->script_end();
        }
        $smtp = new ExpiringAccounts();
        $this->console_writer->print(['Send notifications to admins']);
        $smtp->sendExpiredAccountToAdmins($expiringTokens);
        $this->console_writer->print(['Deleting accounts']);
        foreach ($expiringTokens as $token) {
            $smtp->sendAccountDeletionInfo($token);
            $this->console_writer->print([sprintf('Account %d:%s notified', $token->user->id, $token->user->username)]);
            $token->user->erase();
            $this->console_writer->print([sprintf('Account %d:%s deleted', $token->user->id, $token->user->username)], true);
        }
        $this->console_writer->setPattern('%d %s');
        $this->console_writer->print([sizeof($expiringTokens), 'Accounts deleted'], true);
        $this->script_end();
    }

    public function renotifyUnactivatedAccounts1Days() {
        $this->script_start(__METHOD__);
        $this->_renotifyUnactivatedAccounts(1);
        $this->script_end();
    }

    public function renotifyUnactivatedAccounts3Days() {
        $this->script_start(__METHOD__);
        $this->_renotifyUnactivatedAccounts(3);
        $this->script_end();
    }

    public function renotifyUnactivatedAccounts7Days() {
        $this->script_start(__METHOD__);
        $this->_renotifyUnactivatedAccounts(7);
        $this->script_end();
    }

    public function renotifyUnactivatedAccounts(\Base $f3) {
        $this->script_start(__METHOD__);
        $days = $f3->get('PARAMS.days');
        $this->_renotifyUnactivatedAccounts($days);
        $this->script_end();
    }

    protected function _renotifyUnactivatedAccounts($days) {
        if (!ctype_digit("$days")) {
            $this->console_writer->print(['"days" parameter must be integer'], true);
            $this->script_end();
            exit(1);
        }
        $this->console_writer->print([sprintf('Finding accounts expiring in %d days', $days)]);
        $activation = new AccountActivationTokenModel();
        $sql = <<<SQL
used = ?
AND created_on_datetime + cast(? as interval) < NOW() - cast(? as interval)
AND EXTRACT(DAY FROM NOW() - created_on_datetime) = ?
AND (
    last_notification_datetime = ?
    OR last_notification_datetime < NOW() - cast(? as interval)
)
SQL;
        $expiringTokens = $activation->find([
            $sql,
            AccountActivationTokenModel::TOKEN_UNUSED,
            GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY.' DAY',
            $days.' DAY',
            $days,
            null,
            '1 DAY',
        ]);

        if ($expiringTokens === false) {
            $this->console_writer->print(['No accounts need to be notified'], true);
            $this->script_end();
        }
        $smtp = new ExpiringAccounts();
        $this->console_writer->print(['Send notifications to users']);
        $this->console_writer->setPattern('Notify account %d: %s');
        foreach ($expiringTokens ?: [] as $token) {
            $this->console_writer->print([$token->user->id, $token->user->username], true);
            $smtp->sendAboutToExpireAgain($token);
        }
        $this->console_writer->setPattern('%d %s');
        $this->console_writer->print([sizeof($expiringTokens), 'Accounts notified'], true);
        $this->script_end();
    }
}
