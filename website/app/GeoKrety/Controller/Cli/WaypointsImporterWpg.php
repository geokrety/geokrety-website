<?php

namespace GeoKrety\Controller\Cli;

use Exception;
use GeoKrety\Service\File;
use SimpleXMLElement;

class WaypointsImporterWpg extends WaypointsImporterBaseTopografix {
    const WPG_API_ENDPOINT = 'http://wpg.alleycat.pl/allwps.php';

    const SCRIPT_CODE = 'WPG';
    const SCRIPT_NAME = 'waypoint_importer_wpg';

    public function process() {
        $this->start();
        try {
            $this->perform_topografix_incremental_update(self::WPG_API_ENDPOINT);
        } catch (Exception $exception) {
            echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
        }
        $this->end();
    }

    /**
     * Extract waypoint name from other fields.
     *
     * @return string The extracted waypoint
     */
    protected function _wpt_extractor(?SimpleXMLElement $cache): string {
        $name = $this->string_cleaner($cache->name);

        return sprintf('WPG%s', substr(explode(' ', $name)[0], 3));
    }

    /**
     * Extract cache name from other fields.
     *
     * @return mixed
     */
    protected function _name_extractor(?SimpleXMLElement $cache): string {
        return $this->string_cleaner($cache->name);
    }

    /**
     * Download and parse xml.
     *
     * @param $url string The url to download from
     *
     * @return \SimpleXMLElement The parsed xml
     *
     * @throws Exception
     */
    protected function download_xml(string $url): SimpleXMLElement {
        $tmp_file = tmpfile();
        $path = stream_get_meta_data($tmp_file)['uri'];
        File::download($url, $path);
        $xml_raw = strtr(file_get_contents($path), ['&' => '+']);

        return simplexml_load_string($xml_raw, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_NOCDATA);
    }
}
