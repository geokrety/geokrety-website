<?php

namespace Geokrety\Service;

/**
 * LanguageService : manage languages.
 *
 * https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
 */
class LanguageService {
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

    private static $_instance = null;
    private $isoCodesFactory = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new LanguageService();
            self::$_instance->isoCodesFactory = new \Sokil\IsoCodes\IsoCodesFactory();
        }

        return self::$_instance;
    }

    public static function isLanguageSupported($langAlpha2) {
        return in_array($langAlpha2, self::SUPPORTED_LANGUAGES);
    }

    public static function getSupportedLanguages($locale = false) {
        $isoCodes = self::getInstance()->isoCodesFactory;
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
        $isoCodes = self::getInstance()->isoCodesFactory;
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
