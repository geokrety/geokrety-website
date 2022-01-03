<?php

use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Service\Smarty;

trait OAuthProviderLoader {
    protected SocialAuthProvider $oauthProvider;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        $oauthProvider = new SocialAuthProvider();
        $oauthProvider->load(['lower(name) = ?', $f3->get('PARAMS.strategy')]);
        if ($oauthProvider->dry()) {
            $f3->error(404, _('This provider does not exist.'));
        }
        $this->oauthProvider = $oauthProvider;
        Smarty::assign('oauth_provider', $oauthProvider);
    }
}
