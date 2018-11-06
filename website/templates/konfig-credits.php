<?php

if (!isset($config)) {
    $config = array();
}

$config['gk_credits'][] = [
    'name' => _('name'),
    'icon' => _('logo'),
    'link' => _('link'),
    'desc' => _('decription'),
];
$config['gk_credits'][] = [
    'name' => 'php',
    'icon' => CONFIG_CDN_LOGOS.'php-power-white.gif',
    'link' => 'https://secure.php.net/',
    'desc' => 'server-side scripting language',
];
$config['gk_credits'][] = [
    'name' => 'php extras',
    'desc' => '<a href="https://getcomposer.org/">composer</a>, <a href="https://www.smarty.net/docsv2/fr/">smarty</a>, <a href="https://phpunit.de/">phpunit</a>, <a href="https://github.com/FriendsOfPHP/PHP-CS-Fixer">FriendsOfPHP/PHP-CS-Fixer</a>, <a href="https://github.com/Torann/json-ld">Torann/json-ld</a>',
];
$config['gk_credits'][] = [
    'name' => 'apache',
    'icon' => CONFIG_CDN_LOGOS.'Apache_Server_2.jpg',
    'link' => 'https://httpd.apache.org/',
    'desc' => 'web server application (and extensions)',
];
$config['gk_credits'][] = [
    'name' => 'mariadb',
    'icon' => CONFIG_CDN_LOGOS.'MariaDB-Foundation-vertical-small.png',
    'link' => 'https://mariadb.org',
    'desc' => 'relational database management system',
];
$config['gk_credits'][] = [
    'name' => 'docker',
    'icon' => CONFIG_CDN_LOGOS.'vertical.png',
    'link' => 'https://www.docker.com',
    'desc' => 'operating-system-level virtualization',
];
$config['gk_credits'][] = [
    'name' => 'git',
    'icon' => CONFIG_CDN_LOGOS.'2color-lightbg%402x.png',
    'link' => 'https://git-scm.com',
    'desc' => 'source code management',
];
$config['gk_credits'][] = [
    'name' => 'github',
    'icon' => CONFIG_CDN_LOGOS.'Octocat.png',
    'link' => 'https://github.com',
    'desc' => 'project management, hosting',
];
$config['gk_credits'][] = [
    'name' => 'travis',
    'icon' => CONFIG_CDN_LOGOS.'TravisCI-Mascot-pride.png',
    'icon_width' => '70px',
    'link' => 'https://docs.travis-ci.com/',
    'desc' => 'continuous integration',
];
$config['gk_credits'][] = [
    'name' => 'codacy',
    'icon' => CONFIG_CDN_LOGOS.'logo-codacy.png',
    'icon_style' => 'background: rgba(34, 47, 69, 1);',
    'icon_width' => '130px',
    'link' => 'https://opensource.codacy.com',
    'desc' => 'manage code quality',
];
$config['gk_credits'][] = [
    'name' => 'crowdin',
    'icon' => CONFIG_CDN_LOGOS.'crowdin-TranslationManagementService-logo.png',
    'icon_width' => '130px',
    'link' => 'https://crowdin.com',
    'desc' => 'manage translations',
];
$config['gk_credits'][] = [
    'name' => 'adnanh/webhook',
    'icon' => CONFIG_CDN_LOGOS.'logo-128x128.png',
    'link' => 'https://github.com/adnanh/webhook',
    'desc' => 'configurable incoming webhook server',
];
$config['gk_credits'][] = [
    'name' => 'Sentry',
    'icon' => CONFIG_CDN_LOGOS.'sentry-glyph-black.svg',
    'link' => 'https://sentry.io/welcome/',
    'desc' => 'Open-source error tracking',
];
$config['gk_credits'][] = [
    'name' => 'NASA SRTM',
    'link' => 'https://www2.jpl.nasa.gov/srtm/',
    'desc' => 'SRTM-30 to get the altitudes',
];
$config['gk_credits'][] = [
    'name' => 'Bootstrap',
    'icon' => CONFIG_CDN_LOGOS.'bootstrap-solid.svg',
    'icon_width' => '50px',
    'link' => 'https://getbootstrap.com/',
    'desc' => 'HTML, CSS, and JS framework',
];
$config['gk_credits'][] = [
    'name' => 'flag-icon-css',
    'icon' => CONFIG_CDN_LOGOS.'flag-icon-css.jpg',
    'link' => 'http://flag-icon-css.lip.is/',
    'desc' => 'A collection of all country flags in SVG — plus the CSS',
];
$config['gk_credits'][] = [
    'name' => 'openstreetmap',
    'icon' => CONFIG_CDN_LOGOS.'Public-images-osm_logo.svg',
    'icon_width' => '70px',
    'link' => 'https://openstreetmap.org',
    'desc' => 'Map',
];
$config['gk_credits'][] = [
    'name' => 'robot framework',
    'icon' => CONFIG_CDN_LOGOS.'robot-framework.png',
    'icon_width' => '70px',
    'link' => 'http://robotframework.org/',
    'desc' => 'Automated tests (used by <a href="https://github.com/geokrety/geokrety-website-qa">geokrety-website-qa</a>)',
];
$config['gk_credits'][] = [
    'name' => 'reCAPTCHA',
    'icon' => CONFIG_CDN_LOGOS.'RecaptchaLogo.svg',
    'icon_width' => '70px',
    'link' => 'https://www.google.com/recaptcha/',
    'desc' => 'Secure captcha (stop bots)',
];
