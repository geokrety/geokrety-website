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
        $content = file_get_contents($url);
        if ($content === false || empty($content)) {
            return DEFAULT_COUNTRY_CODE;
        }

        return strtolower($content);
    }

    public static function getCountryName($countryCode) {
        $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();

        return $isoCodes->getCountries()->getByAlpha2(strtoupper($countryCode))->getLocalName();
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
