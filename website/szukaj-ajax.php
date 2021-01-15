<?php

require_once '__sentry.php';

function unexpectedError($message) {
    try {
        $return['tresc'] = '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" id="szukaj_img_error_unexpected" width="16" height="16" /> '.$message;
        echo json_encode($return);
    } catch (Exception $jExc) {
        echo $message;
    }
}

try {
    // śćńółżź

    // MySQL
require_once 'wybierz_jezyk.php'; // choose the user's language
require 'templates/konfig.php';    // config

function waypointLinkLabel($waypoint, $name) {
    if (trim($name) !== '') {
        return trim($name);
    }

    return sprintf(_('waypoint %s'), strtoupper($waypoint));
}

    /**
     * json result (tresc) to send when ONE cache is selected.
     */
    function cacheOk($waypoint, $cache_link, $name, $owner, $cache_type, $cache_country, $countryCode) {
        $flagPlaceholder = '';
        if (trim($countryCode) !== '') {
            $flagPlaceholder = '<img src="'.CONFIG_CDN_IMAGES.'/country-codes/'.$countryCode.'.png" width="16" height="11"'
                         .' alt="'._('flag').'" title="'.$countryCode.'"/>';
        }
        if (trim($owner) !== '') {
            $ownerPlaceholder = '('.sprintf(_('by %s'), $owner).')';
        }

        $result = '<img src="'.CONFIG_CDN_IMAGES.'/icons/ok.png" alt="'._('OK').'" id="szukaj_img_ok" width="16" height="16" /> <a href="'.$cache_link.'" target="_opencaching">'.waypointLinkLabel($waypoint, $name).' <i class="glyphicon glyphicon-link"></i></a> '.$ownerPlaceholder.'<br />';
        if (trim($cache_type) !== '') {
            $result .= sprintf(_('cache type: %s'), _($cache_type)).'<br/>';
        }
        if (trim($cache_country) !== '') {
            $result .= sprintf(_('country: %s %s'), $flagPlaceholder, _($cache_country));
        } elseif (trim($countryCode) !== '') {
            $result .= sprintf(_('country: %s'), $flagPlaceholder);
        }

        return $result;
    }

    /**
     * json result (tresc) to send when NO cache found.
     */
    function noCacheFound() {
        return '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" id="szukaj_img_error_no_cache" width="16" height="16" /> '._('No cache found');
    }
    /**
     * json result (tresc) to send when cache code is unknown.
     */
    function cacheUnknown() {
        return '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" id="szukaj_img_error_missing" width="16" height="16" /> '._('Missing or invalid coordinates');
    }

    /**
     * json result (tresc) to send when selected cache is not associated with lat/lon.
     */
    function cacheWithoutLatLon($waypoint, $name, $cache_link) {
        $link_wpt = '<a href="'.$cache_link.'" target="_opencaching">'.waypointLinkLabel($waypoint, $name).' <i class="glyphicon glyphicon-link"></i></a>';

        return '<img src="'.CONFIG_CDN_IMAGES.'/icons/info3.png" alt="info" id="szukaj_img_info_provide" width="16" height="16" /> '.sprintf(_('Please provide the coordinates (lat/lon) of the cache %s in the "coordinates" input box.'), $link_wpt).'<br />'.
        sprintf(_('<a href="%s">Learn more about hiding geokrets in GC caches</a>'), $config['adres'].'help.php#locationdlagc');
    }

    function handleException($exc) {
        $errorId = uniqid('GKIE_');
        $errorMessage = 'Unexpected error '.$errorId.' :'.$exc->getMessage();
        error_log($errorMessage);
        error_log($exc);
        unexpectedError($errorMessage);
    }

    if ($_REQUEST['skad'] == 'ajax') {
        $link = GKDB::getLink();

        // język

        $lang = $_COOKIE['geokret1'];
        //setlocale(LC_MESSAGES , $lang);
        //setlocale(LC_NUMERIC , 'en_EN');
        bindtextdomain('messages', BINDTEXTDOMAIN_PATH);
        bind_textdomain_codeset('messages', 'UTF-8');
        textdomain('messages');

        if (!empty($_REQUEST['nr'])) { // **************************************** geokret
            $where = '';
            $nr = $_REQUEST['nr'];

            if (preg_match('/^[a-zA-Z0-9]{6}$/', $nr)) {
                $where = "WHERE gk.nr = '$nr' LIMIT 1";
            } else {
                if (preg_match("/^[a-zA-Z0-9]{6}(\.[a-zA-Z0-9]{6})*$/", $nr)) {
                    $nr = str_replace('.', "','", $nr);
                    $where = "WHERE gk.nr IN ('$nr')";
                }
            }

            if ($where != '') {
                $sql = "SELECT gk.id, gk.typ, gk.nazwa, us.userid, us.user, ru.data, ru.waypoint, ru.logtype, wpt.name, ru.lat, ru.lon
					FROM `gk-geokrety` gk
					LEFT JOIN `gk-users` us ON us.userid = gk.owner
					LEFT JOIN `gk-ruchy` ru ON ru.ruch_id=gk.ost_pozycja_id
					LEFT JOIN `gk-waypointy` wpt ON wpt.waypoint = ru.waypoint
					$where";
                $result = mysqli_query($link, $sql);
                $ret = '';

                if (mysqli_num_rows($result) == 0) {
                    echo "<img src='".CONFIG_CDN_IMAGES."/icons/error.png' alt='error' id='szukaj_img_error_gk_not_found' width='16' height='16' /> "._('GeoKret not found');
                    exit;
                } else {
                    while ($row = mysqli_fetch_array($result)) {
                        list($id, $typ, $nazwa, $userid, $username, $data, $waypoint, $logtype, $name, $lat, $lon) = $row;

                        if ($waypoint == '') {
                            $opis = "$lat, $lon";
                        } else {
                            $opis = "$waypoint $name ($data)";
                        }

                        if ($logtype != '') {
                            $lastlog = _('Last log:')." <img src='".CONFIG_CDN_IMAGES."/log-icons/$typ/2$logtype.png' alt='logtype' title='log' /> $opis";
                        } else {
                            $lastlog = '';
                        }

                        if ($ret != '') {
                            $ret .= '<br />';
                        }
                        $ret .= "<img src='".CONFIG_CDN_IMAGES."/icons/ok.png' alt='OK' id='szukaj_img_ok_gk_".$id."' width='16' height='16' /> ".sprintf(_('%s by %s.'), "<a href='/konkret.php?id=$id'>$nazwa</a>", "<a href='/mypage.php?userid=$userid'>$username</a>")." $lastlog";
                    }
                }
                echo $ret;
            } else {
                echo "<img src='".CONFIG_CDN_IMAGES."/icons/error.png' alt='error' id='szukaj_img_error_invalid_tracking' width='16' height='16' /> "._('Invalid tracking code');
            }
        } elseif (!empty($_REQUEST['wpt'])) { // ****************************************  waypoint
            try {
                $return['lat'] = '';
                $return['lon'] = '';
                $wpt = mysqli_real_escape_string($link, $_REQUEST['wpt']);
                $waypointy = new \Geokrety\Repository\WaypointyRepository($link, false);
                $hasResult = $waypointy->getByWaypoint($wpt);
                if (!$hasResult) {
                    $return['tresc'] = cacheUnknown();
                    echo json_encode($return);
                } else {
                    $lat = $waypointy->lat;
                    $lon = $waypointy->lon;

                    if (!empty(trim($lat)) and !empty(trim($lon))) {
                        $return['tresc'] = cacheOk($waypointy->waypoint, $waypointy->cache_link, $waypointy->name,
                      $waypointy->owner, $waypointy->cache_type, $waypointy->country, $waypointy->country_code);
                        $return['lat'] = $lat;
                        $return['lon'] = $lon;
                        echo json_encode($return);
                    } else {
                        $return['tresc'] = cacheWithoutLatLon($waypointy->waypoint, $waypointy->name, $waypointy->cache_link);
                        echo json_encode($return);
                    }
                }
            } catch (Exception $exc) {
                $return['tresc'] = handleException($exc);
                $return['lat'] = '';
                $return['lon'] = '';
                echo json_encode($return);
            }
        } elseif (!empty($_REQUEST['NazwaSkrzynki']) and mb_strlen($_REQUEST['NazwaSkrzynki']) < CONFIG_WAYPOINTY_MIN_LENGTH) {
            $return['tresc'] = '<img src="'.CONFIG_CDN_IMAGES.'/icons/error.png" alt="error" id="szukaj_img_error_5car" width="16" height="16" /> '.sprintf(_('Enter at least %d characters'), CONFIG_WAYPOINTY_MIN_LENGTH);
            echo json_encode($return);
        } elseif (!empty($_REQUEST['NazwaSkrzynki'])) {
            $waypointy = new \Geokrety\Repository\WaypointyRepository($link, false);
            try {
                $return['lat'] = '';
                $return['lon'] = '';
                $byNameCount = $waypointy->countDistinctName($_REQUEST['NazwaSkrzynki']);
                $return['IleSkrzynek'] = $byNameCount;

                if ($byNameCount == 0) {// no result
                    $return['tresc'] = noCacheFound();
                } elseif ($byNameCount > 1) {
                    $maxCache = 4;
                    if ($byNameCount < $maxCache) {
                        $return['tresc'] = _('caches match').':<br />';
                    } else {
                        $return['tresc'] = sprintf(_('%d caches match (the first %d)'), $byNameCount, $maxCache).':<br />';
                    }
                    // listing
                    $sql = "SELECT `waypoint`, `name`, `owner`, `typ`, `kraj`, `link`, `lat`, `lon` FROM `gk-waypointy` WHERE `name` LIKE '%".mysqli_real_escape_string($link, $_REQUEST['NazwaSkrzynki'])."%' LIMIT $maxCache";
                    $result = mysqli_query($link, $sql);
                    $result_index = 0;
                    while ($row = mysqli_fetch_array($result)) {
                        list($waypoint, $name, $owner, $typ, $kraj, $cache_link, $lat, $lon) = $row;
                        $ownerPlaceholder = '';
                        if (trim($owner) !== '') {
                            $ownerPlaceholder = ' ('.sprintf(_('by %s'), $owner).')';
                        }
                        $return['tresc'] .= '<a href="#" id="szukaj_wpt_'.$result_index.'" onclick="document.getElementById(\'wpt\').value = \''.$waypoint.'\'; sprawdzWpt(); return false;"><i class="glyphicon glyphicon-pushpin"></i> '.$waypoint.'</a> - <span class="bardzomale"><a href="'.$cache_link.'" id="szukaj_cache_link_'.$result_index.'" target="_opencaching">'.$name.' <i class="glyphicon glyphicon-link"></i></a>'.$ownerPlaceholder.'</span><br />';
                        ++$result_index;
                    }
                } else { // == 1
                    $hasResult = $waypointy->getByName($_REQUEST['NazwaSkrzynki']);
                    if (!$hasResult) {// never the case
                        $return['tresc'] = cacheUnknown();
                    } elseif (!empty(trim($waypointy->lat)) and !empty(trim($waypointy->lon))) {
                        $return['tresc'] = cacheOk($waypointy->waypoint, $waypointy->cache_link, $waypointy->name,
                      $waypointy->owner, $waypointy->cache_type, $waypointy->country, $waypointy->country_code);
                        $return['lat'] = $waypointy->lat;
                        $return['lon'] = $waypointy->lon;
                        $return['wpt'] = $waypointy->waypoint;
                    } else {
                        $return['tresc'] = cacheWithoutLatLon($waypointy->waypoint, $waypointy->name, $waypointy->cache_link);
                        $return['wpt'] = $waypointy->waypoint;
                    }
                } // jeśli jest jedna skrzynka
            } catch (Exception $exc) {
                $return['tresc'] = handleException($exc);
                $return['lat'] = '';
                $return['lon'] = '';
            }
            echo json_encode($return);
        }
        mysqli_close($link);
        $link = null; // prevent possible warning from smarty.php
    }
} catch (Exception $exc) {
    unexpectedError('Service unavailable - '.$exc->getMessage());
}
