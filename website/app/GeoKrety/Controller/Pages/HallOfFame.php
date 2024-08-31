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
            'icon' => GK_CDN_LOGOS_URL.'/php.svg',
            'icon_width' => '70px',
            'link' => 'https://secure.php.net/',
            'desc' => 'server-side scripting language',
        ];
        $credits[] = [
            'name' => 'php extras',
            'desc' => '<a href="https://getcomposer.org/">composer</a>, <a href="https://www.smarty.net/docsv2/fr/">smarty</a>, <a href="https://phpunit.de/">phpunit</a>, <a href="https://github.com/FriendsOfPHP/PHP-CS-Fixer">FriendsOfPHP/PHP-CS-Fixer</a>, <a href="https://github.com/Torann/json-ld">Torann/json-ld</a>',
        ];
        $credits[] = [
            'name' => 'nginx',
            'icon' => GK_CDN_LOGOS_URL.'/nginx.svg',
            'icon_width' => '70px',
            'link' => 'https://httpd.apache.org/',
            'desc' => 'web server application (and extensions)',
        ];
        $credits[] = [
            'name' => 'Postgresql',
            'icon' => GK_CDN_LOGOS_URL.'/postgresql.svg',
            'icon_width' => '70px',
            'link' => 'https://www.postgresql.org/',
            'desc' => 'The World\'s Most Advanced Open Source Relational Database',
        ];
        $credits[] = [
            'name' => 'docker',
            'icon' => GK_CDN_LOGOS_URL.'/docker.svg',
            'icon_width' => '70px',
            'link' => 'https://www.docker.com',
            'desc' => 'operating-system-level virtualization',
        ];
        $credits[] = [
            'name' => 'git',
            'icon' => GK_CDN_LOGOS_URL.'/git.svg',
            'icon_width' => '70px',
            'link' => 'https://git-scm.com',
            'desc' => 'source code management',
        ];
        $credits[] = [
            'name' => 'github',
            'icon' => GK_CDN_LOGOS_URL.'/octocat.svg',
            'icon_width' => '70px',
            'link' => 'https://github.com',
            'desc' => 'project management, hosting',
        ];
        $credits[] = [
            'name' => 'codacy',
            'icon' => GK_CDN_LOGOS_URL.'/codacy.svg',
            'icon_width' => '70px',
            'link' => 'https://opensource.codacy.com',
            'desc' => 'manage code quality',
        ];
        $credits[] = [
            'name' => 'crowdin',
            'icon' => GK_CDN_LOGOS_URL.'/crowdin.svg',
            'icon_width' => '70px',
            'link' => 'https://crowdin.com',
            'desc' => 'manage translations',
        ];
        $credits[] = [
            'name' => 'adnanh/webhook',
            'icon' => GK_CDN_LOGOS_URL.'/webhook.svg',
            'icon_width' => '70px',
            'link' => 'https://github.com/adnanh/webhook',
            'desc' => 'configurable incoming webhook server',
        ];
        $credits[] = [
            'name' => 'Sentry',
            'icon' => GK_CDN_LOGOS_URL.'/sentry.svg',
            'icon_width' => '70px',
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
            'icon' => GK_CDN_LOGOS_URL.'/bootstrap.svg',
            'icon_width' => '70px',
            'link' => 'https://getbootstrap.com/',
            'desc' => 'HTML, CSS, and JS framework',
        ];
        $credits[] = [
            'name' => 'flag-icon-css',
            'icon' => GK_CDN_LOGOS_URL.'/flag-icon.svg',
            'icon_width' => '100px',
            'link' => 'https://flagicons.lipis.dev/',
            'desc' => 'A curated collection of all country flags in SVG — plus the CSS for easier integration',
        ];
        $credits[] = [
            'name' => 'OpenStreetMap',
            'icon' => GK_CDN_LOGOS_URL.'/osm.svg',
            'icon_width' => '70px',
            'link' => 'https://openstreetmap.org',
            'desc' => 'Map',
        ];
        $credits[] = [
            'name' => 'Robot Framework',
            'icon' => GK_CDN_LOGOS_URL.'/robot-framework.svg',
            'icon_width' => '70px',
            'link' => 'http://robotframework.org/',
            'desc' => 'Automated tests (used by <a href="https://github.com/geokrety/geokrety-website-qa">geokrety-website-qa</a>)',
        ];
        $credits[] = [
            'name' => 'Libravatar',
            'icon' => GK_CDN_LOGOS_URL.'/libravatar.svg',
            'icon_width' => '70px',
            'link' => 'https://www.libravatar.org/',
            'desc' => 'Libravatar is a service which delivers your avatar (profile picture) to other websites.',
        ];
        $credits[] = [
            'name' => 'reCAPTCHA',
            'icon' => GK_CDN_LOGOS_URL.'/recaptcha.svg',
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
            'icon_width' => '70px',
            'link' => 'https://fatfreeframework.com',
            'desc' => 'PHP micro-framework',
        ];
        $credits[] = [
            'name' => 'Google Noto Color Emoji',
            'icon' => GK_CDN_LOGOS_URL.'/noto-emoji.svg',
            'icon_width' => '50px',
            'link' => 'https://github.com/googlefonts/noto-emoji',
            'desc' => 'Noto Emoji fonts',
        ];
        $credits[] = [
            'name' => 'SVG Repo',
            'icon' => GK_CDN_LOGOS_URL.'/svg-repo.svg',
            'icon_width' => '100px',
            'link' => 'https://www.svgrepo.com/',
            'desc' => '500.000+ Open-licensed SVG Vector and Icons',
        ];
        $credits[] = [
            'name' => 'JetBrains',
            'icon' => GK_CDN_LOGOS_URL.'/jetbrains.svg',
            'icon_width' => '70px',
            'link' => 'https://www.jetbrains.com/',
            'desc' => '"A rich suite of tools that provide an exceptional developer experience"',
        ];
        $credits[] = [
            'name' => 'UptimeRobot',
            'icon' => GK_CDN_LOGOS_URL.'/uptimerobot-logo-dark.svg',
            'icon_width' => '100px',
            'link' => 'https://uptimerobot.com/',
            'desc' => '"The world\'s leading uptime monitoring service"',
        ];
        Smarty::assign('app_credits', $credits);

        Smarty::render('pages/hall_of_fame.tpl');
    }
}
