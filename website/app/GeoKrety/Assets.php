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
        $recaptchaHosts = 'https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/';
        if (\Multilang::instance()->current === 'inline-translation') {
            header(
                'Content-Security-Policy: '
                .sprintf('script-src \'nonce-%s\' \'strict-dynamic\'; ', $nonce)
                .sprintf('img-src \'self\' data: blob: %s %s https://www.gstatic.com/recaptcha/ https://tile.openstreetmap.org https://cdn.crowdin.com/jipt/images/ https://seccdn.libravatar.org/avatar/ https://crowdin-static.downloads.crowdin.com/avatar/ https://cdn.geokrety.org; ', GK_CDN_SERVER_URL, GK_MINIO_SERVER_URL_EXTERNAL)
                .'frame-src https://www.google.com/ https://www.youtube.com/ https://crowdin.com/; '
                .sprintf('style-src \'self\' \'nonce-%s\'; ', $nonce)
                .sprintf('style-src-elem \'self\' \'unsafe-inline\' %s https://cdn.crowdin.com/jipt/jipt.css https://fonts.googleapis.com/css; ', GK_CDN_SERVER_URL)
                .'style-src-attr \'self\' \'unsafe-inline\'; '
                .sprintf('connect-src \'self\' %s https://crowdin.com/api/v2/jipt/cookie https://crowdin.com/api/v2/jipt/project/geokrety https://crowdin.com/api/v2/jipt/project/geokrety/strings; ', GK_MINIO_SERVER_URL_EXTERNAL)
                ."worker-src 'self' blob: $recaptchaHosts; "
                ."child-src 'self' blob: $recaptchaHosts; "
            );

            return;
        }
        header(
            'Content-Security-Policy: '
            .sprintf('script-src \'nonce-%s\' \'strict-dynamic\'; ', $nonce)
            .sprintf('img-src \'self\' data: blob: %s %s https://www.gstatic.com/recaptcha/ https://tile.openstreetmap.org https://seccdn.libravatar.org/avatar/ https://cdn.geokrety.org; ', GK_CDN_SERVER_URL, GK_MINIO_SERVER_URL_EXTERNAL)
            .'frame-src https://www.google.com/ https://www.youtube.com/; '
            .sprintf('style-src \'self\' \'nonce-%s\'; ', $nonce)
            .sprintf('style-src-elem \'self\' \'unsafe-inline\' %s; ', GK_CDN_SERVER_URL)
            .'style-src-attr \'self\' \'unsafe-inline\'; '
            .sprintf('connect-src \'self\' %s; ', GK_MINIO_SERVER_URL_EXTERNAL)
            ."worker-src 'self' blob: $recaptchaHosts; "
            ."child-src 'self' blob: $recaptchaHosts; "
        );
    }

    public function addJsAsync($path, $priority = 5, $group = 'footer', $slot = null, $params = []) {
        $params_ = ['async' => ''];
        if (count($params) > 0) {
            $params_ = array_merge($params_, $params);
        }
        $this->addJs($path, $priority, $group, $slot, $params_);
    }

    public function addJs($path, $priority = 5, $group = 'footer', $slot = null, $params = []) {
        $params_ = ['nonce' => $this->f3->get('NONCE')];
        if (count($params) > 0) {
            $params_ = array_merge($params_, $params);
        }
        $this->add($path, 'js', $group, $priority, $slot, $params_);
    }

    public function addCss($path, $priority = 5, $group = 'head', $slot = null, $params = []) {
        $params_ = ['nonce' => $this->f3->get('NONCE')];
        if (count($params) > 0) {
            $params_ = array_merge($params_, $params);
        }
        $this->add($path, 'css', $group, $priority, $slot, $params_);
    }
}
