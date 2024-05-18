<?php

namespace GeoKrety;

/**
 * Extended Asset class to include automatic CSP header and nonce support.
 */
class Assets extends \Assets {
    public function __construct(?Template $template = null) {
        parent::__construct($template);
        // https://github.com/google/recaptcha/blob/master/examples/recaptcha-content-security-policy.php
        // https://stackoverflow.com/a/53890878/944936
        $nonce = new \Delatbabel\ApiSecurity\Generators\Nonce();
        $nonce = $nonce->getNonce();
        $this->f3->set('NONCE', $nonce);
        header(
            'Content-Security-Policy: '
            .sprintf('script-src \'nonce-%s\' \'strict-dynamic\'; ', $nonce)
            .sprintf('img-src \'self\' data: %s %s https://www.gstatic.com/recaptcha/ https://tile.openstreetmap.org; ', GK_CDN_SERVER_URL, GK_MINIO_SERVER_URL_EXTERNAL)
            .'frame-src https://www.google.com/; '
            .sprintf('style-src \'self\' \'nonce-%s\'; ', $nonce)
            .sprintf('style-src-elem \'self\' \'unsafe-inline\' %s; ', GK_CDN_SERVER_URL)
            .'style-src-attr \'self\' \'unsafe-inline\'; '
            .sprintf('connect-src \'self\' %s; ', GK_MINIO_SERVER_URL_EXTERNAL)
        );
    }

    public function addJs($path, $priority = 5, $group = 'footer', $slot = null) {
        $this->add($path, 'js', $group, $priority, $slot, ['nonce' => $this->f3->get('NONCE')]);
    }

    public function addCss($path, $priority = 5, $group = 'head', $slot = null) {
        $this->add($path, 'css', $group, $priority, $slot, ['nonce' => $this->f3->get('NONCE')]);
    }
}
