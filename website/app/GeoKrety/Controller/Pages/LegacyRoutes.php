<?php

namespace GeoKrety\Controller;

use GeoKrety\LogType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Model\Picture;
use GeoKrety\Service\Moves as MovesService;
use GeoKrety\Service\RateLimit;
use GeoKrety\Service\Xml\Error;
use GeoKrety\Service\Xml\MovesSuccess;

class LegacyRoutes {
    public const LEGACY_MOVE_CREATE_FIELDS_MAP = [
        'wpt' => 'waypoint',
        'latlon' => 'coordinates',
        'nr' => 'tracking_code',
        'data' => 'date',
        'godzina' => 'hour',
        'minuta' => 'minute',
    ];

    // https://new-theme.staging.geokrety.org/konkret.php?id=8426
    // https://new-theme.staging.geokrety.org/konkret.php?gk=GK20EA
    public function konkret(\Base $f3) {
        $gkid = null;

        if ($f3->exists('GET.id')) {
            $id = $f3->get('GET.id');
            if (!ctype_digit($id)) {
                $f3->error('400', '"id" must be numeric');
            }
            $gkid = Geokret::id2gkid($id);
        }
        if ($f3->exists('GET.gk')) {
            $gkid = strtoupper($f3->get('GET.gk'));
            if (is_numeric($gkid)) {
                $gkid = Geokret::id2gkid($gkid);
            }
        }

        if (is_null($gkid)) {
            $f3->error('400', '"id" or "gk" parameter must be provided');
        }
        $f3->reroute(['geokret_details', ['gkid' => $gkid]], $permanent = true);
    }

    // https://new-theme.staging.geokrety.org/mypage.php?userid=26422&co=0
    public function mypage(\Base $f3) {
        $userid = null;
        if (!$f3->exists('GET.userid')) {
            if ($f3->exists('SESSION.CURRENT_USER')) {
                $userid = $f3->get('SESSION.CURRENT_USER');
            } else {
                $f3->reroute(['home'], $permanent = false, $die = true);
            }
        } else {
            $userid = $f3->get('GET.userid');
        }
        $page = $f3->get('GET.co');
        switch ($page) {
            case 1:
                $f3->reroute(['user_owned', ['userid' => $userid]], $permanent = true, $die = true);
                break;
            case 2:
                $f3->reroute(['user_watched', ['userid' => $userid]], $permanent = true, $die = true);
                break;
            case 3:
                $f3->reroute(['user_recent_moves', ['userid' => $userid]], $permanent = true, $die = true);
                break;
            case 4:
                $f3->reroute(['user_owned_recent_moves', ['userid' => $userid]], $permanent = true, $die = true);
                break;
            case 5:
                $f3->reroute(['user_inventory', ['userid' => $userid]], $permanent = true, $die = true);
                break;
            case 0:
            default:
                $f3->reroute(['user_details', ['userid' => $userid]], $permanent = true, $die = true);
        }
    }

    private function _export_query_params(\Base $f3, ?array $others = null): string {
        $params = [
            'modifiedsince' => $f3->get('GET.modifiedsince'),
            'bypass_password' => $f3->get('GET.kocham_kaczynskiego'),
            'timezone' => $f3->get('GET.timezone') ?: 'Europe/Paris',
            'compress' => $f3->get('GET.gzip') ? 'gzip' : ($f3->get('GET.bzip2') ? 'bzip2' : false),
        ];

        return http_build_query(array_merge($params, $others ?? []));
    }

    public function export(\Base $f3) {
        $url_params = $this->_export_query_params($f3);
        $f3->reroute(sprintf('@api_v1_export?%s', $url_params), $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/export2.php?inventory=1&secid=
    // https://new-theme.staging.geokrety.org/export2.php?wpt=OP99LG

    public function export2(\Base $f3) {
        $others = [
            'userid' => $f3->get('GET.userid'),
            'gkid' => $f3->get('GET.gkid'),
            'wpt' => $f3->get('GET.wpt'),
            'lonSW' => $f3->get('GET.lonSW'),
            'latSW' => $f3->get('GET.latSW'),
            'lonNE' => $f3->get('GET.lonNE'),
            'latNE' => $f3->get('GET.latNE'),
            'secid' => $f3->get('GET.secid'),
            'inventory' => $f3->get('GET.inventory'),
            'details' => $f3->get('GET.details'),
        ];
        if ($f3->exists('GET.rate_limits_bypass')) {
            $others['rate_limits_bypass'] = $f3->get('GET.rate_limits_bypass');
        }
        if ($f3->exists('GET.short_lived_session_token')) {
            $others['short_lived_session_token'] = $f3->get('GET.short_lived_session_token');
        }
        $url_params = $this->_export_query_params($f3, $others);
        $f3->reroute(sprintf('@api_v1_export2?%s', $url_params), $permanent = false, $die = true);
    }

    public function export_oc(\Base $f3) {
        $url_params = $this->_export_query_params($f3);
        $f3->reroute(sprintf('@api_v1_export_oc?%s', $url_params), $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/gkt/search_v3.php?mode=latlon&lat=45.22078&lon=5.7622
    public function gkt_search_v3(\Base $f3) {
        $params = [
            'lat' => $f3->get('GET.lat'),
            'lon' => $f3->get('GET.lon'),
        ];
        $url_params = http_build_query($params);
        $f3->reroute(sprintf('@gkt_v3_search?%s', $url_params), $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/gkt/inventory_v3.php
    public function gkt_inventory_v3(\Base $f3) {
        $f3->reroute('@gkt_v3_inventory', $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/ruchy.php?nr=xxxx&logtype=0&wpt=GC8888&latlon=43.69365 6.86097
    // https://new-theme.staging.geokrety.org/ruchy.php?gkt=drop_gc&nr=xxxx&waypoint=GC8888&lat=43.69365&lon=6.86097
    public function ruchy(\Base $f3) {
        $params = [
            'tracking_code' => $f3->get('GET.nr'),
            'waypoint' => $f3->get('GET.wpt') ?: ($f3->get('GET.waypoint') ?: null),
            'coordinates' => $f3->get('GET.latlon') ?: (($f3->exists('GET.lat') and $f3->exists('GET.lon')) ? sprintf('%s %s', $f3->get('GET.lat'), $f3->get('GET.lon')) : null),
            'move_type' => $f3->get('GET.logtype') ?? ($f3->get('GET.gkt') === 'drop_gc' ? LogType::LOG_TYPE_DROPPED : null),
        ];
        $url_params = http_build_query($params);
        $f3->reroute(sprintf('@move_create?%s', $url_params), $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/ruchy.php POST
    public function ruchy_post(\Base $f3) {
        RateLimit::check_rate_limit_xml('API_LEGACY_MOVE_POST', $f3->get('POST.secid'));

        // Translate from legacy names
        foreach (self::LEGACY_MOVE_CREATE_FIELDS_MAP as $old => $new) {
            if (!$f3->exists("POST.$new")) {
                $f3->copy("POST.$old", "POST.$new");
                $f3->clear("POST.$old");
            }
        }
        if (!$f3->exists('POST.tz')) {
            // Set default to Europe/Paris as compatibility with legacy GKv1 API
            // See https://github.com/cgeo/cgeo/issues/9496
            $f3->set('POST.tz', 'Europe/Paris');
        }

        $login = new Login();
        $login->secidAuth($f3, $f3->get('POST.secid'));

        $move_data = MovesService::postToArray($f3);
        $move_service = new MovesService();
        [$moves, $errors] = $move_service->toMoves($move_data, new Move());

        if (sizeof($errors) > 0) {
            Login::disconnectUser($f3);
            Error::buildError(true, $errors);
            exit;
        }

        // Save the moves
        try {
            foreach ($moves as $_move) {
                /* @var $_move Move */
                $_move->save();
            }
        } catch (\Exception $e) {
            Login::disconnectUser($f3);
            Error::buildError(true, $e->getMessage());
            exit;
        }
        Login::disconnectUser($f3);
        MovesSuccess::buildSuccess(true, $moves);
    }

    // https://new-theme.staging.geokrety.org/templates/medal-pi.png
    public function templates_medals(\Base $f3) {
        $f3->reroute(sprintf('%s/images/medals/medal-%s', GK_CDN_SERVER_URL, $f3->get('PARAMS.medal')), $permanent = true, $die = true);
    }

    // https://new-theme.staging.geokrety.org/templates/badges/top100-mover-2012.png
    public function templates_badges(\Base $f3) {
        $f3->reroute(sprintf('%s/images/badges/%s', GK_CDN_SERVER_URL, $f3->get('PARAMS.badge')), $permanent = true, $die = true);
    }

    // https://new-theme.staging.geokrety.org/statpics/3807.png
    public function statpics(\Base $f3) {
        $f3->reroute(sprintf('%s/statpic/%s', GK_MINIO_SERVER_URL_EXTERNAL, $f3->get('PARAMS.statpic')), $permanent = true, $die = true);
    }

    // https://new-theme.staging.geokrety.org/obrazki/1512592236mg2p5.jpg
    public function obrazki(\Base $f3) {
        RateLimit::check_rate_limit_xml('API_LEGACY_PICTURE_PROXY', $f3->get('GET.secid'));

        $picture = new Picture();
        $picture->load(['filename = :file or key = :file', ':file' => $f3->get('PARAMS.picture')]);
        if ($picture->valid()) {
            $f3->reroute($picture->get_url(), $permanent = true, $die = true);
        }
        http_response_code(404);
        header('Content-type: image/svg+xml');
        echo <<<EOT
<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg">
<text x="0" y="15" fill="red">No such picture</text>
</svg>
EOT;
    }

    // https://new-theme.staging.geokrety.org/index.php?lang=en_EN.UTF-8
    public function index(\Base $f3) {
        $f3->reroute(['home'], $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/adduser.php
    public function adduser(\Base $f3) {
        // Keep old spammer away
        if ($f3->exists('POST.submit')) {
            \Sugar\Event::instance()->emit('user.create-spam', $f3->get('POST'));
            \Flash::instance()->addMessage('Account successfully created', 'success');
            $f3->reroute('@home', die: true);
        }
        $f3->reroute(['registration'], die: true);
    }

    // https://new-theme.staging.geokrety.org/longin.php
    public function longin(\Base $f3) {
        $f3->reroute(['login'], $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/claim.php
    public function claim(\Base $f3) {
        $f3->reroute(['geokret_claim'], $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/georss.php?userid=26988
    public function georss(\Base $f3) {
        echo 'TODO';
        http_response_code(404);
    }

    // https://new-theme.staging.geokrety.org/api-login2secid.php
    public function login2secid(\Base $f3) {
        $f3->reroute(['api_v1_login2secid'], $permanent = false, $die = true);
    }

    // https://new-theme.staging.geokrety.org/api-login2secid.php
    public function login2secid_post(\Base $f3) {
        $login = new Login();
        $login->login2Secid_post($f3);
    }

    // https://new-theme.staging.geokrety.org/szukaj.php?wpt=OP866L
    public function szukaj(\Base $f3) {
        $waypoint = $f3->get('GET.wpt');
        if (is_null($waypoint)) {
            http_response_code(400);
            exit(_('Waypoint parameter must be provided.'));
        }
        $f3->reroute(['search_by_waypoint', ['waypoint' => $waypoint]], $permanent = true, $die = true);
    }

    // http://geokrety.org/m/qr.php?nr=<TRACKING_CODE>
    public function qr(\Base $f3) {
        $tracking_code = $f3->get('GET.nr');
        if (is_null($tracking_code)) {
            http_response_code(400);
            exit(_('"nr" parameter must be provided.'));
        }
        $f3->reroute(sprintf('@move_create?%s', http_build_query(['tracking_code' => $tracking_code])));
    }
}
