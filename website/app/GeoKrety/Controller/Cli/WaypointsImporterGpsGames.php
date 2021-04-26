<?php

namespace GeoKrety\Controller\Cli;

class WaypointsImporterGpsGames extends WaypointsImporterBaseTopografix {
    const GPSG_API_ENDPOINT = 'http://geocaching.gpsgames.org/cgi-bin/ge.pl';
    //const GPSG_CACHE_DETAIL_URL = 'http://geodashing.gpsgames.org/cgi-bin/dp.pl?dp=%s';

    const SCRIPT_CODE = 'GPS_GAMES';
    protected string $class_name = __CLASS__;

    /**
     * @throws \Exception
     */
    public function process() {
        $url_params = http_build_query([
            'download' => 'Download',
            'downloadformat' => 'GPX',
            'limit' => 1000,
        ]);
        $this->perform_topografix_incremental_update(self::GPSG_API_ENDPOINT, $url_params);
    }
}
