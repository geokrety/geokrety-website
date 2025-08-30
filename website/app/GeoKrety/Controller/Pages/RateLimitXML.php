<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\RateLimit;
use GeoKrety\Service\StorageException;
use GeoKrety\Service\Xml\RateLimits;

class RateLimitXML extends BaseXML {
    private RateLimits $xml;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);
        $this->xml = new RateLimits(true, $f3->get('GET.compress'));
    }

    public function authenticate(): array {
        $keys = [];
        if ($this->f3->exists('GET.secid')) {
            $login = new Login();

            // TODO can we use that to load user limits?
            $user = $login->secidAuth($this->f3, $this->f3->get('GET.secid'), false);
            if (!is_null($user)) {
                $keys[] = $user->secid;
                Login::disconnectUser($this->f3);
                // return $keys;
            }
        }

        $keys[] = \Base::instance()->get('IP');

        return $keys;
    }

    public function get(\Base $f3) {
        $id = $this->f3->exists('GET.secid') ? $this->f3->get('GET.secid') : \Base::instance()->get('IP');
        RateLimit::check_rate_limit_xml('API_V1_CHECK_RATE_LIMIT', $id);

        $xml = $this->xml;
        try {
            $keys = $this->authenticate();

            $usages = RateLimit::get_usage_for_identities($keys);
            $originalByNorm = [];
            foreach ($keys as $k) {
                $originalByNorm[strtr($k, [':' => '_'])] = $k;
            }
            foreach (GK_RATE_LIMITS as $name => $values) {
                $this->xml->addLimit($name, $values[0], $values[1]);
                if (!isset($usages[$name])) {
                    foreach ($keys as $k) {
                        $this->xml->addUsage($k, 0);
                    }
                    $this->xml->endElement();
                    continue;
                }
                foreach ($usages[$name] as $normKey => $count) {
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
