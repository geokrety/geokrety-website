<?php

function go2geo($waypoint) {
    // ------------------------------------------------------------------------ 2 characters

    // opencaching
    $prefiksy['2']['OP'] = 'https://opencaching.pl/searchplugin.php?sourceid=waypoint-search&userinput=';
    $prefiksy['2']['OC'] = 'https://www.opencaching.de/searchplugin.php?sourceid=waypoint-search&userinput=';
    $prefiksy['2']['OK'] = 'https://opencache.uk/searchplugin.php?sourceid=waypoint-search&userinput=';
    $prefiksy['2']['OB'] = 'https://www.opencaching.nl/searchplugin.php?sourceid=waypoint-search&userinput=';
    $prefiksy['2']['OR'] = 'https://www.opencaching.ro/searchplugin.php?sourceid=waypoint-search&userinput=';
    $prefiksy['2']['OZ'] = 'https://opencaching.cz/searchplugin.php?sourceid=waypoint-search&userinput=';
    $prefiksy['2']['OU'] = 'https://www.opencaching.us/searchplugin.php?sourceid=waypoint-search&userinput=';

    // australia
    $prefiksy['2']['GA'] = 'https://geocaching.com.au/cache/';

    // geocaching.com
    $prefiksy['2']['GC'] = 'https://www.geocaching.com/seek/cache_details.aspx?wp=';

    // terra http://www.terracaching.com/viewcache.cgi?C=TCCWU
    // terra https://play.terracaching.com/Cache/TC8VXQ
    $prefiksy['2']['TC'] = 'https://play.terracaching.com/Cache/';
    // romania
    $prefiksy['2']['GR'] = 'https://geocaching.plus.ro/modules.php?name=News&file=article&sid=';
    $prefiksy['2']['RH'] = 'https://www.rejtekhely.ro/modules.php?name=News&file=article&sid=';

    // geocaching at gpsgames.org http://geocaching.gpsgames.org/cgi-bin/ge.pl?wp=GE0174
    $prefiksy['2']['GE'] = 'https://geocaching.gpsgames.org/cgi-bin/ge.pl?wp=';

    // geodashing GDnn-XXXX http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Geodashing  eg GD99-MANK
    $prefiksy['2']['GD'] = 'https://geodashing.gpsgames.org/cgi-bin/dp.pl?dp=';

    // shutterspot
    $prefiksy['2']['SH'] = 'https://shutterspot.gpsgames.org/cgi-bin/sh.pl?cacheID=';

    // waymaking http://www.waymarking.com/waymarks/WM78XF
    $prefiksy['2']['WM'] = 'https://www.waymarking.com/waymarks/';

    // trigpoints
    $prefiksy['2']['TP'] = 'https://trigpointinguk.com/trigs/trig-details.php?t=';

    // geokrety
    $prefiksy['2']['GK'] = 'https://geokrety.org/konkret.php?gk=';

    // travelbugi TB2ZV5M
    $prefiksy['2']['TB'] = 'https://www.geocaching.com/track/details.aspx?tracker=';

    // geocaching.com
    $prefiksy['2']['OX'] = 'https://www.opencaching.com/#geocache/';

    // ------------------------------------------------------------------------ 3 characters
    // waypoint game: WPG300
    // $prefiksy['3']['WPG'] = 'http://wpg.alleycat.pl/waypoint.php?wp=';

    // russian VI/6569
    $prefiksy['3']['GE/'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['VI/'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['MS/'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['TR/'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['EX/'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['2']['GE'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['2']['VI'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['2']['MS'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['2']['TR'] = 'https://www.geocaching.su/?pn=101&cid=';
    $prefiksy['2']['EX'] = 'https://www.geocaching.su/?pn=101&cid=';

    // ------------------------------------------------------------------------ 1 character
    // navicache
    $prefiksy['1']['N'] = 'https://www.navicache.com/cgi-bin/db/displaycache2.pl?CacheID=';  // + dec

    // http://www.travelertags.com/index.php?option=com_travelview&func=details&tid=62658   = TF4C2
    $prefiksy['1']['T'] = 'https://www.travelertags.com/index.php?option=com_travelview&func=details&tid='; // +dec

    // waypoints, that sites need hex2dec conversion
    $prefiksy_dec = ['N', 'SH', 'T'];

    // waypoints, that need waypoint (number) without prefix
    $prefiksy_sufiksy = ['GE/', 'VI/', 'MS/', 'TR/', 'EX/', 'GE', 'VI', 'MS', 'TR', 'EX', 'GR', 'RH', 'TP'];

    // --- order of chceking:
    $check_order = [2, 3, 1];

    // -------------------------------------------------- START ------------------------------------- //

    $waypoint = strtoupper($waypoint);

    foreach ($check_order as $cut) {
        $prefiks = substr($waypoint, 0, $cut);
        $sufiks = substr($waypoint, $cut, 256);

        if (array_key_exists($prefiks, $prefiksy[$cut])) {      // if link exists in prefiks table
            $link = $prefiksy[$cut][$prefiks];
            $id = $waypoint;

            // if hex need to be converted to dec
            if (in_array($prefiks, $prefiksy_dec) == true) {
                if (!ctype_xdigit($sufiks)) {
                    continue;
                }
                $id = hexdec($sufiks);
            }

            // just number, without prefix
            elseif (in_array($prefiks, $prefiksy_sufiksy) == true) {
                $id = $sufiks;
            }

            $link = $link.$id;

            return $link;
            exit;
        }
    }
}
