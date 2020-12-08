<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class Help extends Base {
    public function get($f3) {
        $social_groups = [];
        $social_groups[] = [
            'link' => 'https://www.facebook.com/groups/1624761011150615/about/',
            'service' => 'Facebook',
            'title' => 'GeoKret en France',
            'lang' => 'fr',
        ];
        $social_groups[] = [
            'link' => 'https://www.facebook.com/groups/317897208552636/about/',
            'service' => 'Facebook',
            'title' => 'GeoCaching met GeoKrety.Org',
            'lang' => 'nl',
        ];
        // Historical group
        // $social_groups[] = [
        //     'link' => 'https://plus.google.com/communities/100975961916549394786',
        //     'service' => 'Google+',
        //     'title' => 'GeoKrety Discussion',
        //     'lang' => array('en', 'de', 'nl'),
        // ];
        $social_groups[] = [
            'link' => 'https://forum.opencaching.pl/viewforum.php?f=11',
            'service' => 'Opencaching Poland',
            'title' => 'GeoKrety',
            'lang' => 'pl',
        ];
        $social_groups[] = [
            'link' => 'https://geoclub.de/forum/viewforum.php?f=102',
            'service' => 'geoclub.de forum',
            'title' => 'Geokrety',
            'lang' => 'de',
        ];
        $social_groups[] = [
            'link' => 'https://forum.opencaching.nl/viewforum.php?f=33',
            'service' => 'Opencaching Benelux',
            'title' => 'GeoKrety',
            'lang' => 'nl',
        ];
        Smarty::assign('social_groups', $social_groups);

        foreach (explode(',', $f3->get('LANGUAGE')) as $lang) {
            $file = 'help-pages/'.$lang.'/help.html';
            if (file_exists(GK_SMARTY_TEMPLATES_DIR.'/'.$file)) {
                Smarty::assign('file', $file);
                Smarty::render('pages/help.tpl');
                exit();
            }
        }
        StaticPages::_404($f3);
    }
}
