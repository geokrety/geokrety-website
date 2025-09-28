<?php

namespace GeoKrety;

use GeoKrety\Service\SecurityHeaders;

/**
 * Extended Asset class to include automatic CSP header and nonce support.
 */
class Assets extends \Assets {
    public function __construct(?Template $template = null) {
        parent::__construct($template);
    }

    public function addJsAsync($path, $priority = 5, $group = 'footer', $slot = null, $params = []) {
        $params_ = ['async' => ''];
        if (count($params) > 0) {
            $params_ = array_merge($params_, $params);
        }
        $this->addJs($path, $priority, $group, $slot, $params_);
    }

    public function addJs($path, $priority = 5, $group = 'footer', $slot = null, $params = []) {
        $params_ = ['nonce' => SecurityHeaders::instance()->getNonce()];
        if (count($params) > 0) {
            $params_ = array_merge($params_, $params);
        }
        $this->add($path, 'js', $group, $priority, $slot, $params_);
    }

    public function addCss($path, $priority = 5, $group = 'head', $slot = null, $params = []) {
        $params_ = ['nonce' => SecurityHeaders::instance()->getNonce()];
        if (count($params) > 0) {
            $params_ = array_merge($params_, $params);
        }
        $this->add($path, 'css', $group, $priority, $slot, $params_);
    }
}
