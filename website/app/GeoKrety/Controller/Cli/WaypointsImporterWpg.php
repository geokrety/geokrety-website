<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Service\File;

class WaypointsImporterWpg extends WaypointsImporterBaseTopografix {
    public const WPG_API_ENDPOINT = 'http://wpg.alleycat.pl/allwps.php';

    public const SCRIPT_CODE = 'WPG';
    protected string $class_name = __CLASS__;

    /**
     * @throws \Exception
     */
    public function process() {
        $this->perform_topografix_incremental_update(self::WPG_API_ENDPOINT);
    }

    /**
     * Extract waypoint name from other fields.
     *
     * @return string The extracted waypoint
     */
    protected function _wpt_extractor(?\SimpleXMLElement $cache): string {
        $name = $this->string_cleaner($cache->name);

        return sprintf('WPG%s', substr(explode(' ', $name)[0], 3));
    }

    /**
     * Extract cache name from other fields.
     *
     * @return mixed
     */
    protected function _name_extractor(?\SimpleXMLElement $cache): string {
        return $this->string_cleaner($cache->name);
    }

    /**
     * Download and parse xml.
     *
     * @param $url string The url to download from
     *
     * @return \SimpleXMLElement The parsed xml
     *
     * @throws \Exception
     */
    protected function download_xml(string $url): \SimpleXMLElement {
        $tmp_file = tmpfile();
        $path = stream_get_meta_data($tmp_file)['uri'];
        File::download($url, $path);
        $xml_raw = strtr(file_get_contents($path), ['&' => '+']);

        return simplexml_load_string($xml_raw, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_NOCDATA);
    }
}
