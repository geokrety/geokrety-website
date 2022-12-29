<?php

namespace GeoKrety\Service;

class MatomoTracker extends \MatomoTracker {
    /**
     * Customize the timeout to be in milliseconds.
     */
    protected function prepareCurlOptions($url, $method, $data, $forcePostUrlEncoded) {
        $options = parent::prepareCurlOptions($url, $method, $data, $forcePostUrlEncoded);
        $options[CURLOPT_NOSIGNAL] = 1; // https://www.php.net/manual/en/function.curl-setopt.php#104597
        $options[CURLOPT_TIMEOUT_MS] = GK_PIWIK_CONNECT_TIMEOUT_MS;
        unset($options[CURLOPT_TIMEOUT]);

        return $options;
    }
}
