<?php

namespace GeoKrety\Service;

/**
 * LanguageService : manage languages.
 *
 * https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
 * /usr/share/i18n/SUPPORTED
 */
class LanguageService extends \Prefab {
    public const DEFAULT_LANGUAGE_CODE = 'en';
    public const SUPPORTED_LANGUAGES = [
        'en', 'fr', 'de', 'ru', 'nl', 'pl',
        'bg', 'ca', 'da', 'el', 'es', 'et',
        'fi', 'cs', 'hu', 'it', 'ja', 'nb',
        'se', 'nn', 'pt', 'zh', 'ro', 'sk',
        'sq', 'sr', 'sv', 'th', 'tr', 'uk',
    ];
    public const SUPPORTED_LANGUAGES_LOCAL_NAME = [
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
        'ja' => '日本人',
        'nb' => 'Norsk bokmål',
        'nl' => 'Nederlands',
        'nn' => 'Norsk nynorsk',
        'ph' => 'Pilipinas',
        'pl' => 'Polski',
        'pt' => 'Português',
        'ro' => 'Română',
        'ru' => 'Русский',
        'se' => 'Davvisámegiella',
        'sk' => 'Slovenčina',
        'sq' => 'Shqip',
        'sr' => 'српски језик',
        'sv' => 'Svenska',
        'th' => 'ไทย',
        'tr' => 'Türk',
        'uk' => 'Українська',
        'inline-translation' => 'Translation mode',
    ];
    private $isoCodesFactory = null;

    public function __construct() {
        $this->isoCodesFactory = new \Sokil\IsoCodes\IsoCodesFactory();
    }

    public static function isLanguageSupported($langAlpha2) {
        return in_array($langAlpha2, self::SUPPORTED_LANGUAGES);
    }

    public static function areLanguageSupported(?array $langAlpha2) {
        return is_null($langAlpha2) || !array_diff($langAlpha2, self::SUPPORTED_LANGUAGES);
    }

    public static function getSupportedLanguages($locale = false) {
        $isoCodes = self::instance()->isoCodesFactory;
        $langs = $isoCodes->getLanguages();

        $languages = [];
        foreach (self::SUPPORTED_LANGUAGES as $langAlpha2) {
            if ($locale) {
                $languages[$langAlpha2] = self::SUPPORTED_LANGUAGES_LOCAL_NAME[$langAlpha2];
            } else {
                $languages[$langAlpha2] = $langs->getByAlpha2($langAlpha2)->getLocalName();
            }
        }

        return $languages;
    }

    public static function getLanguageByAlpha2($langAlpha2, $locale = false) {
        if ($locale) {
            return self::SUPPORTED_LANGUAGES_LOCAL_NAME[$langAlpha2];
        }
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

        return $languages->getByAlpha2($langAlpha2)->getLocalName();
    }

    public static function translate(string $text, array $languages): array {
        $response = [];
        foreach ($languages as $language) {
            self::changeLanguageTo($language);
            $response[] = gettext($text);
        }
        self::restoreLanguageToCurrentChosen();

        return $response;
    }

    public static function changeLanguageTo($langAlpha2) {
        if (is_null($langAlpha2)) {
            $langAlpha2 = self::DEFAULT_LANGUAGE_CODE;
        }

        \Base::instance()->set('LANGUAGE', \Multilang::instance()->locales()[$langAlpha2]);
        \Carbon\Carbon::setLocale($langAlpha2);
    }

    public static function restoreLanguageToCurrentChosen() {
        \Base::instance()->set('LANGUAGE', \Multilang::instance()->locales()[\Multilang::instance()->current]);
        \Carbon\Carbon::setLocale(\Multilang::instance()->current);
    }
}
