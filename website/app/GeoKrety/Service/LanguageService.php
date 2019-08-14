<?php

namespace GeoKrety\Service;

/**
 * LanguageService : manage languages.
 *
 * https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
 */
class LanguageService extends \Prefab {

    private $isoCodesFactory = null;

    const DEFAULT_LANGUAGE_CODE = 'en';

    const SUPPORTED_LANGUAGES = array(
        'en', 'fr', 'de', 'pl', 'bg', 'ca',
        'da', 'el', 'es', 'et', 'fi', 'cs',
        'hu', 'it', 'nl', 'pt', 'zh',
        'ro', 'ru', 'sk', 'sq', 'sv', 'th',
        'tr', 'uk',
    );

    const SUPPORTED_LANGUAGES_LOCAL_NAME = array(
        'en' => 'English',
        'bg' => 'Български',
        'ca' => 'Català',
        'zh' => 'Chinese',
        'cs' => 'Česky',
        'de' => 'Deutsch',
        'da' => 'Dansk',
        'el' => 'Ελληνικά',
        'es' => 'Español',
        'et' => 'Eesti',
        'fi' => 'Suomi',
        'fr' => 'Français',
        'hu' => 'Magyar',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'ph' => 'Pilipinas',
        'pl' => 'Polski',
        'pt' => 'Português',
        'ro' => 'Română',
        'ru' => 'Русский',
        'sk' => 'Slovenčina',
        'sq' => 'Shqip',
        'sv' => 'Svenska',
        'th' => 'ไทย',
        'tr' => 'Türk',
        'uk' => 'Українська',
    );

    public function __construct() {
        $this->isoCodesFactory = new \Sokil\IsoCodes\IsoCodesFactory();
    }

    public static function isLanguageSupported($langAlpha2) {
        return in_array($langAlpha2, self::SUPPORTED_LANGUAGES);
    }

    public static function getSupportedLanguages($locale = false) {
        $isoCodes = self::instance()->isoCodesFactory;
        $langs = $isoCodes->getLanguages();

        $languages = array();
        foreach (self::SUPPORTED_LANGUAGES as $langAlpha2) {
            if ($locale) {
                $languages[$langAlpha2] = self::SUPPORTED_LANGUAGES_LOCAL_NAME[$langAlpha2];
            } else {
                $languages[$langAlpha2] = $langs->getByAlpha2($langAlpha2)->getLocalName();
            }
        }

        return $languages;
    }

    public static function getLanguageByAlpha2($langAlpha2) {
        $isoCodes = self::instance()->isoCodesFactory;
        $languages = $isoCodes->getLanguages();

        // Some workarounds for database errors
        if ($langAlpha2 == 'cz') {
            $langAlpha2 = 'cs';
        }
        if ($langAlpha2 == 'cn') {
            $langAlpha2 = 'zh';
        }
        if ($langAlpha2 == 'dk') {
            $langAlpha2 = 'da';
        }

        return $languages->getByAlpha2($langAlpha2);
    }
}
