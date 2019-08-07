<?php

namespace Geokrety\Service;

const DEFAULT_COUNTRY_CODE = 'xyz';

/**
 * CountryService : return country code from coordinates.
 */
class CountryService extends AbstractValidationService {
    public static function getCountryCode($coordinates) {
        if (is_null($coordinates)) {
            return DEFAULT_COUNTRY_CODE;
        }
        if (!is_array($coordinates)) {
            throw new \InvalidArgumentException('coordinates parameter is expected to be an array');
        }
        if (!array_key_exists('lat', $coordinates) || !array_key_exists('lon', $coordinates)) {
            return DEFAULT_COUNTRY_CODE;
        }

        $url = sprintf(SERVICE_REVERSE_COUNTRY_GEOCODER, $coordinates['lat'], $coordinates['lon']);
        $country = $content = file_get_contents($url);
        if ($content === false || empty($content)) {
            // fallback
            $url = sprintf(SERVICE_REVERSE_COUNTRY_GEOCODER_GOOGLE, $coordinates['lat'], $coordinates['lon'], GOOGLE_MAP_KEY);
            $content = file_get_contents($url);

            // Really give up
            if ($content === false || empty($content)) {
                return null;
            }

            // Process retrieved data from google
            $jsondata = json_decode($content, true);
            if (is_array($jsondata) and $jsondata['status'] == 'OK') {
                $data = array();
                foreach ($jsondata['results']['0']['address_components'] as $element) {
                    $data[implode(' ', $element['types'])] = $element['short_name'];
                }

                $country = $data['country political'];
            }

        }
        return strtolower($country);
    }

    public static function getCountryName($countryCode) {
        $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();

        $alpha2 = $isoCodes->getCountries()->getByAlpha2(strtoupper($countryCode));
        if (is_null($alpha2)) {
            return 'Unknown';
        }

        return $alpha2->getLocalName();
    }

    public static function getLanguageName($lang) {
        $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
        $languages = $isoCodes->getLanguages();
        // Some workarounds database errors
        if ($lang == 'cz') {
            $lang = 'cs';
        }
        $language = $languages->getByAlpha2($lang);

        return $language->getName();
    }
}
