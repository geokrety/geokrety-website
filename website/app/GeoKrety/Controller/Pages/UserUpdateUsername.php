<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\RateLimit;
use GeoKrety\Service\RateLimitExceeded;
use GeoKrety\Service\Smarty;
use GeoKrety\Session;
use Sugar\Event;

class UserUpdateUsername extends Base {
    use \CurrentUserLoader;

    public function post(\Base $f3) {
        $this->check_account_status($f3);

        $f3->get('DB')->begin();
        $context = ['old_username' => $this->currentUser->username];
        $newUsername = $f3->get('POST.username');
        Smarty::assign('newUsername', $newUsername);
        $this->currentUser->username = $newUsername;

        // reCaptcha
        $this->checkCaptcha();
        $this->checkCsrf();

        // Check
        if (!$this->currentUser->validate()) {
            $f3->get('DB')->rollback();
            $this->get($f3);
            exit;
        }
        try {
            RateLimit::incr('USERNAME_CHANGE', $this->currentUser->id);
        } catch (RateLimitExceeded $e) {
            register_shutdown_function('GeoKrety\Model\AuditPost::AmendAuditPostWithErrors', 'Rate limit exceeded');
            \Flash::instance()->addMessage(sprintf(_('You can only change your username %d times per month'), GK_RATE_LIMITS['USERNAME_CHANGE'][0]), 'danger');
            $f3->get('DB')->rollback();
            $this->get($f3);
            exit;
        }

        // Save
        $this->currentUser->save();
        $f3->get('DB')->commit();
        Event::instance()->emit('user.renamed', $this->currentUser, $context);
        Login::disconnectUser($f3);
        Session::closeAllSessionsForUser($this->currentUser);
        \Flash::instance()->addMessage(_('Username changed. Please login again.'), 'success');
        $f3->reroute('@home');
    }

    public function get(\Base $f3) {
        $this->check_account_status($f3);
        Smarty::render('pages/user_update_username.tpl');
    }

    private function check_account_status(\Base $f3): void {
        if (!$this->current_user->isEmailValid() or !$this->current_user->hasEmail() or $this->current_user->isAccountInvalid()) {
            \Flash::instance()->addMessage(_('Sorry, to use this feature, you must have a valid registered email address.'), 'danger');
            if (GK_DEVEL) {
                $this->current_user->resendAccountActivationEmail();
                $f3->reroute(['user_details', ['userid' => $this->current_user->id]]);
            }
            $this->current_user->resendAccountActivationEmail(true);
            $f3->reroute(['user_details', ['userid' => $this->current_user->id]], false, false);
            $f3->abort();
            $this->current_user->resendAccountActivationEmail();
        }
    }
}
