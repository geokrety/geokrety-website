<?php

function go2geo($waypoint)
{
    // ------------------------------------------------------------------------ 2 characters

    // opencaching
    $prefiksy['2']['OP'] = 'http://www.opencaching.pl/searchplugin.php?userinput=';
    $prefiksy['2']['OC'] = 'http://www.opencaching.de/searchplugin.php?userinput=';
    $prefiksy['2']['OK'] = 'http://www.opencaching.org.uk/searchplugin.php?userinput=';
    $prefiksy['2']['OZ'] = 'http://www.opencaching.cz/searchplugin.php?userinput=';
    $prefiksy['2']['OS'] = 'http://www.opencaching.se/searchplugin.php?userinput=';
    $prefiksy['2']['ON'] = 'http://www.opencaching.no/searchplugin.php?userinput=';
    $prefiksy['2']['OJ'] = 'http://www.opencaching.jp/searchplugin.php?userinput=';
    $prefiksy['2']['OL'] = 'http://www.opencaching.lv/searchplugin.php?userinput=';
    $prefiksy['2']['OU'] = 'http://www.opencaching.us/searchplugin.php?userinput=';

    // australia
    $prefiksy['2']['GA'] = 'http://geocaching.com.au/cache/';

    // geocaching.com
    $prefiksy['2']['GC'] = 'http://www.geocaching.com/seek/cache_details.aspx?wp=';

    // terra http://www.terracaching.com/viewcache.cgi?C=TCCWU
    $prefiksy['2']['TC'] = 'http://www.terracaching.com/viewcache.cgi?C=';
    // romania
    $prefiksy['2']['GR'] = 'http://geocaching.plus.ro/modules.php?name=News&file=article&sid=';
    $prefiksy['2']['RH'] = 'http://www.rejtekhely.ro/modules.php?name=News&file=article&sid=';

    // geocaching at gpsgames.org http://geocaching.gpsgames.org/cgi-bin/ge.pl?wp=GE0174
    $prefiksy['2']['GE'] = 'http://geocaching.gpsgames.org/cgi-bin/ge.pl?wp=';

    // geodashing GDnn-XXXX http://www.gpsgames.org/index.php?option=com_wrapper&wrap=Geodashing  eg GD99-MANK
    $prefiksy['2']['GD'] = 'http://geodashing.gpsgames.org/cgi-bin/dp.pl?dp=';

    // shutterspot
    $prefiksy['2']['SH'] = 'http://shutterspot.gpsgames.org/cgi-bin/sh.pl?cacheID=';

    // waymaking http://www.waymarking.com/waymarks/WM78XF
    $prefiksy['2']['WM'] = 'http://www.waymarking.com/waymarks/';

    // trigpoints
    $prefiksy['2']['TP'] = 'http://trigpointinguk.com/trigs/trig-details.php?t=';

    // geokrety
    $prefiksy['2']['GK'] = 'https://geokrety.org/konkret.php?gk=';

    // travelbugi TB2ZV5M
    $prefiksy['2']['TB'] = 'http://www.geocaching.com/track/details.aspx?tracker=';

    // geocaching.com
    $prefiksy['2']['OX'] = 'http://www.opencaching.com/#geocache/';

    // ------------------------------------------------------------------------ 3 characters
    // waypoint game: WPG300
    //$prefiksy['3']['WPG'] = 'http://wpg.alleycat.pl/waypoint.php?wp=';

    // russian VI/6569
    $prefiksy['3']['GE/'] = 'http://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['VI/'] = 'http://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['MS/'] = 'http://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['TR/'] = 'http://www.geocaching.su/?pn=101&cid=';
    $prefiksy['3']['EX/'] = 'http://www.geocaching.su/?pn=101&cid=';

    // ------------------------------------------------------------------------ 1 character
    // navicache
    $prefiksy['1']['N'] = 'http://www.navicache.com/cgi-bin/db/displaycache2.pl?CacheID=';  // + dec

    // http://www.travelertags.com/index.php?option=com_travelview&func=details&tid=62658   = TF4C2
    $prefiksy['1']['T'] = 'http://www.travelertags.com/index.php?option=com_travelview&func=details&tid='; // +dec

    // waypoints, that sites need hex2dec conversion
    $prefiksy_dec = array('N', 'SH', 'T');

    // waypoints, that need waypoint (number) without prefix
    $prefiksy_sufiksy = array('GE/', 'VI/', 'MS/', 'TR/', 'EX/', 'GR', 'RH', 'TP');

    // --- order of chceking:
    $check_order = array(2, 3, 1);

    // -------------------------------------------------- START ------------------------------------- //

    $waypoint = strtoupper($waypoint);

    foreach ($check_order as $cut) {
        $prefiks = substr($waypoint, 0, $cut);
        $sufiks = substr($waypoint, $cut, 256);

        //if(is_numeric($sufiks)) echo "is numeric ";

        $link = $prefiksy[$cut][$prefiks];
        if ($link != null) {      // if link exists in prefiks table
            $id = $waypoint;

            // if hex need to be converted to dec
            if (in_array($prefiks, $prefiksy_dec) == true) {
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

    // if above rules didn't work...
    if ($link == null) {
        return null;
    }
}
