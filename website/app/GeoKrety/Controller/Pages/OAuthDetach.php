<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\UserSocialAuth;
use GeoKrety\Service\Smarty;

class OAuthDetach extends Base {
    use \OAuthProviderLoader;

    public string $template = 'dialog/oauth_disconnect.tpl';

    public function get($f3) {
        $this->_get($f3);
        Smarty::render(sprintf('extends:base.tpl|%s', $this->template));
    }

    protected function _get(\Base $f3) {
        if (!$this->current_user->isConnectedWithProvider($this->oauthProvider)) {
            $this->template = 'dialog/oauth_not_connected_to_provider.tpl';
        }
    }

    public function get_ajax(\Base $f3) {
        $this->_get($f3);
        Smarty::render(sprintf('extends:base_modal.tpl|%s', $this->template));
    }

    public function post($f3) {
        $this->checkCsrf();
        $userSocialAuth = new UserSocialAuth();
        if (!$this->current_user->hasPassword()
            && $userSocialAuth->count(['user = ?', $this->current_user->id], null, 0) <= 1) {
            \Flash::instance()->addMessage(join(' ', [
                sprintf(_('You cannot detach from %s as you will not have any other valid authentication method.'), $this->oauthProvider->name),
                _('Please set a password or connect another provider first.'),
            ]), 'danger');
            $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
        }

        $userSocialAuth->load(['user = ? AND provider = ?', $this->current_user->id,  $this->oauthProvider->id]);
        if ($userSocialAuth->dry() or !$userSocialAuth->erase()) {
            \Flash::instance()->addMessage(sprintf(_('Something went wrong while detaching from your %s account. Please contact us.'), $this->oauthProvider->name), 'danger');
        }
        $f3->reroute(sprintf('@user_details(@userid=%d)', $this->current_user->id), $die = true);
    }
}
