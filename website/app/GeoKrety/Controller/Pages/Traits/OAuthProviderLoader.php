<?php

use GeoKrety\Model\SocialAuthProvider;
use GeoKrety\Service\Smarty;

trait OAuthProviderLoader {
    /**
     * @var SocialAuthProvider
     */
    protected $oauthProvider;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        $oauthProvider = new SocialAuthProvider();
        $oauthProvider->load(['lower(name) = ?', $f3->get('PARAMS.strategy')]);
        if ($oauthProvider->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }
        $this->oauthProvider = $oauthProvider;
        Smarty::assign('oauth_provider', $oauthProvider);
    }
}
