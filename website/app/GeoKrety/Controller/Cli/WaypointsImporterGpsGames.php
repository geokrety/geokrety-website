<?php

namespace GeoKrety\Controller\Cli;

use Exception;

class WaypointsImporterGpsGames extends WaypointsImporterBaseTopografix {
    const GPSG_API_ENDPOINT = 'http://geocaching.gpsgames.org/cgi-bin/ge.pl';
    //const GPSG_CACHE_DETAIL_URL = 'http://geodashing.gpsgames.org/cgi-bin/dp.pl?dp=%s';

    const SCRIPT_CODE = 'GPS_GAMES';
    const SCRIPT_NAME = 'waypoint_importer_gps_games';

    public function process() {
        $this->start();
        try {
            $url_params = http_build_query([
                'download' => 'Download',
                'downloadformat' => 'GPX',
                'limit' => 1000,
            ]);
            $this->perform_topografix_incremental_update(self::GPSG_API_ENDPOINT, $url_params);
        } catch (Exception $exception) {
            echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
        }
        $this->end();
    }
}
