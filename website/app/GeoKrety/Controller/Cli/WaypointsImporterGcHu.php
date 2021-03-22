<?php

namespace GeoKrety\Controller\Cli;

use Exception;

class WaypointsImporterGcHu extends WaypointsImporterBaseTopografix {
    const GC_HU_API_ENDPOINT = 'https://www.geocaching.hu/caches.geo';

    const SCRIPT_CODE = 'GC_HU';
    const SCRIPT_NAME = 'waypoint_importer_gc_hu';

    public function process() {
        $this->start();
        try {
            $url_params = http_build_query([
                'egylapon' => 25,
                'filetype' => 'gpx_easygps',
                'waypoint_xml' => '<field name="kod" deaccent="i" case="upper"/>',
                'description_xml' => '<field name="nev"/>',
                'content_type' => 'text/plain; charset=utf-8',
                'content_disposition' => 'inline',
                'filename' => 'gc_hu.xml',
                'compression' => 'default',
                'submit_waypoints' => 'Download',
                'no_poi' => 'i',
                'id' => 'geomap',
            ]);
            $this->perform_topografix_incremental_update(self::GC_HU_API_ENDPOINT, $url_params);
        } catch (Exception $exception) {
            echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
        }
        $this->end();
    }
}
