<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\RateLimit;
use GeoKrety\Service\RateLimitPolicy;
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
                $identities[] = $secid;
            }
            $ip = \Base::instance()->get('IP');
            if ($ip) {
                $identities[] = $ip;
            }

            $usages = RateLimit::get_usage_for_identities($identities);
            $originalByNorm = [];
            foreach ($identities as $id) {
                $originalByNorm[RateLimit::normalizeKey($id)] = $id;
            }

            // if secid resolves to a user, override the display label for that secid
            $userIdForPlan = $secid ? RateLimit::inferUserId($secid) : null;
            if ($secid && $userIdForPlan !== null) {
                $originalByNorm[RateLimit::normalizeKey($secid)] = (string) $userIdForPlan;
            }

            foreach (array_keys(GK_RATE_LIMITS_DEFAULT) as $limitName) {
                [$effLimit, $effPeriod] = RateLimitPolicy::resolve($limitName, $userIdForPlan);
                $this->xml->addLimit($limitName, $effLimit, $effPeriod);

                if (!isset($usages[$limitName])) {
                    foreach ($identities as $id) {
                        $this->xml->addUsage($id, 0);
                    }
                    $this->xml->endElement();
                    continue;
                }

                foreach ($usages[$limitName] as $normKey => $count) {
                    $displayId = $originalByNorm[$normKey] ?? $normKey;
                    $this->xml->addUsage($displayId, $count);
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
