<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class HallOfFame extends Base {
    public const CONTRIBUTORS_IDS = ['kumy', 'BSLLM', 'filips', 'simor', 'Thathanka', 'moose', 'YvesProvence', 'Quinto', 'harrieklomp', 'Lineflyer'];

    public function get() {
        $contributors = [];
        foreach (self::CONTRIBUTORS_IDS as $username) {
            $user = new User();
            $user->load(['lower(username) = lower(?)', $username]);
            $contributors[$username] = $user->valid() ? $user : null;
        }
        Smarty::assign('contributors', $contributors);

        $credits = [];
        // $credits[] = [
        //     'name' => _('name'),
        //     'icon' => _('logo'),
        //     'icon_style' => 'background: rgba(34, 47, 69, 1);',
        //     'icon_width' => '130px',
        //     'link' => _('link'),
        //     'desc' => _('decription'),
        // ];
        $credits[] = [
            'name' => 'php',
            'icon' => GK_CDN_LOGOS_URL.'/php-power-white.gif',
            'link' => 'https://secure.php.net/',
            'desc' => 'server-side scripting language',
        ];
        $credits[] = [
            'name' => 'php extras',
            'desc' => '<a href="https://getcomposer.org/">composer</a>, <a href="https://www.smarty.net/docsv2/fr/">smarty</a>, <a href="https://phpunit.de/">phpunit</a>, <a href="https://github.com/FriendsOfPHP/PHP-CS-Fixer">FriendsOfPHP/PHP-CS-Fixer</a>, <a href="https://github.com/Torann/json-ld">Torann/json-ld</a>',
        ];
        $credits[] = [
            'name' => 'apache',
            'icon' => GK_CDN_LOGOS_URL.'/Apache_Server_2.jpg',
            'link' => 'https://httpd.apache.org/',
            'desc' => 'web server application (and extensions)',
        ];
        $credits[] = [
            'name' => 'mariadb',
            'icon' => GK_CDN_LOGOS_URL.'/MariaDB-Foundation-vertical-small.png',
            'link' => 'https://mariadb.org',
            'desc' => 'relational database management system',
        ];
        $credits[] = [
            'name' => 'docker',
            'icon' => GK_CDN_LOGOS_URL.'/vertical.png',
            'link' => 'https://www.docker.com',
            'desc' => 'operating-system-level virtualization',
        ];
        $credits[] = [
            'name' => 'git',
            'icon' => GK_CDN_LOGOS_URL.'/2color-lightbg%402x.png',
            'link' => 'https://git-scm.com',
            'desc' => 'source code management',
        ];
        $credits[] = [
            'name' => 'github',
            'icon' => GK_CDN_LOGOS_URL.'/Octocat.png',
            'link' => 'https://github.com',
            'desc' => 'project management, hosting',
        ];
        $credits[] = [
            'name' => 'travis',
            'icon' => GK_CDN_LOGOS_URL.'/TravisCI-Mascot-pride.png',
            'icon_width' => '70px',
            'link' => 'https://docs.travis-ci.com/',
            'desc' => 'continuous integration',
        ];
        $credits[] = [
            'name' => 'codacy',
            'icon' => GK_CDN_LOGOS_URL.'/logo-codacy.png',
            'icon_style' => 'background: rgba(34, 47, 69, 1);',
            'icon_width' => '130px',
            'link' => 'https://opensource.codacy.com',
            'desc' => 'manage code quality',
        ];
        $credits[] = [
            'name' => 'crowdin',
            'icon' => GK_CDN_LOGOS_URL.'/crowdin-TranslationManagementService-logo.png',
            'icon_width' => '130px',
            'link' => 'https://crowdin.com',
            'desc' => 'manage translations',
        ];
        $credits[] = [
            'name' => 'adnanh/webhook',
            'icon' => GK_CDN_LOGOS_URL.'/logo-128x128.png',
            'link' => 'https://github.com/adnanh/webhook',
            'desc' => 'configurable incoming webhook server',
        ];
        $credits[] = [
            'name' => 'Sentry',
            'icon' => GK_CDN_LOGOS_URL.'/sentry-glyph-black.svg',
            'link' => 'https://sentry.io/welcome/',
            'desc' => 'Open-source error tracking',
        ];
        $credits[] = [
            'name' => 'NASA SRTM',
            'link' => 'https://www2.jpl.nasa.gov/srtm/',
            'desc' => 'SRTM-30 to get the altitudes',
        ];
        $credits[] = [
            'name' => 'Bootstrap',
            'icon' => GK_CDN_LOGOS_URL.'/bootstrap-solid.svg',
            'icon_width' => '50px',
            'link' => 'https://getbootstrap.com/',
            'desc' => 'HTML, CSS, and JS framework',
        ];
        $credits[] = [
            'name' => 'flag-icon-css',
            'icon' => GK_CDN_LOGOS_URL.'/flag-icon-css.jpg',
            'link' => 'http://flag-icon-css.lip.is/',
            'desc' => 'A collection of all country flags in SVG — plus the CSS',
        ];
        $credits[] = [
            'name' => 'openstreetmap',
            'icon' => GK_CDN_LOGOS_URL.'/Public-images-osm_logo.svg',
            'icon_width' => '70px',
            'link' => 'https://openstreetmap.org',
            'desc' => 'Map',
        ];
        $credits[] = [
            'name' => 'robot framework',
            'icon' => GK_CDN_LOGOS_URL.'/robot-framework.png',
            'icon_width' => '70px',
            'link' => 'http://robotframework.org/',
            'desc' => 'Automated tests (used by <a href="https://github.com/geokrety/geokrety-website-qa">geokrety-website-qa</a>)',
        ];
        $credits[] = [
            'name' => 'Libravatar',
            'icon' => 'https://seccdn.libravatar.org/nobody/65.png',
            'icon_width' => '65px',
            'link' => 'https://www.libravatar.org/',
            'desc' => 'Libravatar is a service which delivers your avatar (profile picture) to other websites.',
        ];
        $credits[] = [
            'name' => 'reCAPTCHA',
            'icon' => GK_CDN_LOGOS_URL.'/RecaptchaLogo.svg',
            'icon_width' => '70px',
            'link' => 'https://www.google.com/recaptcha/',
            'desc' => 'Secure captcha (stop bots)',
        ];
        $credits[] = [
            'name' => 'Phinx',
            'link' => 'https://phinx.org/',
            'desc' => 'PHP Database Migrations For Everyone',
        ];
        $credits[] = [
            'name' => 'Fat-Free Framework',
            'icon' => GK_CDN_LOGOS_URL.'/f3.svg',
            'icon_width' => '130px',
            'link' => 'https://fatfreeframework.com',
            'desc' => 'PHP micro-framework',
        ];
        Smarty::assign('app_credits', $credits);

        Smarty::render('pages/hall_of_fame.tpl');
    }
}
