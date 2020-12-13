<?php

namespace GeoKrety\Service;

class Url extends \Prefab {
    const NO_REDIRECT_URLS = [
        'login',
        'logout',
        'registration',
        'registration_social',
        'registration_activate',
    ];

    /**
     * @param string            $alias  The alias to build url
     * @param string|array|null $params The params for the built url
     *
     * @return string The wanted url, with encoded query string to rebuild the current url. Needed to recompuse url
     *                according the the user preferred language.
     */
//    public static function getGoto(string $alias = 'home', string|array $params) { // php 8.0 will support `union-types`
    public static function serializeGoto(string $alias = 'home', $params = null): string {
        $f3 = \Base::instance();
        $query = [
            'goto' => base64_encode($f3->get('ALIAS')),
            'params' => base64_encode($f3->serialize($f3->get('PARAMS'))),
            'query' => base64_encode($f3->serialize($f3->get('GET'))),
        ];

        return \Base::instance()->alias($alias, $params, $query);
    }

    /**
     * @param string|null $lang A language override
     *
     * @return string|null Rebuild an url from encoded query string
     */
    public static function unserializeGoto(?string $lang): ?string {
        $f3 = \Base::instance();
        if (!$f3->exists('GET.goto')) {
            return null;
        }

        $goto = base64_decode($f3->get('GET.goto'));
        if (in_array($goto, self::NO_REDIRECT_URLS)) {
            return null;
        }

        $query = '';
        if ($f3->exists('GET.query') and !empty(base64_decode($f3->get('GET.query')))) {
            $query = http_build_query($f3->unserialize(base64_decode($f3->get('GET.query'))));
            $query = (!empty($query) ? '?' : '').$query;
        }

        $params = $f3->unserialize(base64_decode($f3->get('GET.params')));
        $ml = \Multilang::instance();

        return $ml->alias($goto, $params, $lang).$query;
    }
}
