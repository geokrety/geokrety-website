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

    public function authenticate(): ?User {
        if ($this->f3->exists('GET.secid')) {
            $login = new Login();

            return $login->secidAuth($this->f3, $this->f3->get('GET.secid'), false);
        }

        return null;
    }

    public function get(\Base $f3) {
        RateLimit::check_rate_limit_xml('API_V1_CHECK_RATE_LIMIT', $this->f3->get('GET.secid'));
        $xml = $this->xml;

        try {
            $keys = [\Base::instance()->get('IP')];
            $user = $this->authenticate();
            if (!is_null($user)) {
                $keys[] = $user->secid;
            }

            $usages = [];
            foreach ($keys as $key) {
                $usages = array_merge_recursive($usages, RateLimit::get_rates_limits_usage(sprintf('*__%s*', $key)));
            }
            foreach (GK_RATE_LIMITS as $name => $values) {
                $this->xml->addLimit($name, $values[0], $values[1]);
                if (!array_key_exists($name, $usages)) {
                    foreach ($keys as $key) {
                        $this->xml->addUsage($key, 0);
                    }
                    $this->xml->endElement();
                    continue;
                }
                foreach ($usages[$name] as $id => $count) {
                    $this->xml->addUsage($id, $count);
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
