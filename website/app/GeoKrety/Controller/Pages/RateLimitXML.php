<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\StorageException;
use GeoKrety\Service\Xml\RateLimits;

class RateLimitXML extends BaseXML {
    private RateLimits $xml;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->xml = new RateLimits(true, $f3->get('GET.compress'));
    }

    public function get(\Base $f3) {
        // Pass secid if present; otherwise null and RateLimit will fallback to IP internally
        $secid = $f3->exists('GET.secid') ? (string) $f3->get('GET.secid') : null;
        RateLimit::check_rate_limit_xml('API_V1_CHECK_RATE_LIMIT', $secid);

        $xml = $this->xml;
        try {
            $identities = [];
            if ($secid !== null && !empty($secid)) {
                $user = User::get_user_by_secid($secid);
                if (!is_null($user)) {
                    $identities[] = $user->id;
                }
            }
            $ip = \Base::instance()->get('IP');
            if ($ip) {
                $identities[] = $ip;
            }

            $usages = RateLimit::get_usage_for_identities($identities);
            foreach ($usages as $limitName => $info) {
                $this->xml->addLimit($limitName, GK_RATE_LIMITS_DEFAULT[$limitName][1]);
                foreach ($info as $key => $details) {
                    $this->xml->addUsage($key, $details['usage'], $details['limit'], $details['tier']);
                }
                $this->xml->endElement();
            }
        } catch (StorageException $e) {
        }

        // Render XML
        $xml->end();
        $xml->finish();
    }
}
