<?php

namespace GeoKrety\Controller\Cli;

class WaypointsImporterGcHu extends WaypointsImporterBaseTopografix {
    public const GC_HU_API_ENDPOINT = 'https://www.geocaching.hu/caches.geo';

    public const SCRIPT_CODE = 'GC_HU';
    protected string $class_name = __CLASS__;

    /**
     * @throws \Exception
     */
    public function process() {
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
        ]);
        $this->perform_topografix_incremental_update(self::GC_HU_API_ENDPOINT, $url_params);
    }
}
