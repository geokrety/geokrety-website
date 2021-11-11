<?php

namespace GeoKrety\Controller\Cli;

use Exception;
use GeoKrety\Model\WaypointOC;
use GeoKrety\Service\ConsoleWriter;
use PDOException;
use SimpleXMLElement;

abstract class WaypointsImporterBaseTopografix extends WaypointsImporterBase {
    /**
     * Handle topografix gpx format.
     *
     * @throws Exception
     */
    protected function perform_topografix_incremental_update(string $api_endpoint, string $url_params = '') {
        echo $this->console_writer->sprintf("*** \e[0;33mRunning full import\e[0m").PHP_EOL;
        ob_flush();

        $nUpdated = 0;
        $nError = 0;

        $xml = $this->download_xml(sprintf('%s?%s', $api_endpoint, $url_params));

        $caches_count = sizeof($xml->wpt);
        if ($caches_count) {
            $console_writer = new ConsoleWriter('Importing cache %7s: %6.2f%% (%s/%d) - %d errors');
            foreach ($xml->wpt as $cache) {
                $waypoint = $this->_wpt_extractor($cache);
                $name = $this->_name_extractor($cache);
                $wpt = new WaypointOC();
                $wpt->load(['waypoint = ?', $waypoint]);
                $wpt->waypoint = $waypoint;
                $wpt->provider = static::SCRIPT_CODE;
                $wpt->name = $name;
                $wpt->lat = number_format(floatval($cache['lat']), 5, '.', '');
                $wpt->lon = number_format(floatval($cache['lon']), 5, '.', '');
                $wpt->link = $this->url_cleaner($cache->url);
                $wpt->type = $this->string_cleaner($cache->type) ?: null;
                $wpt->status = $this->status_to_id($cache->status);
                try {
                    $wpt->save();
                } catch (PDOException $exception) {
                    ++$nError;
                    continue;
                }
                $total = ++$nUpdated + $nError;
                $console_writer->print([$waypoint, $total / $caches_count * 100, $total, $caches_count, $nError]);
            }
            echo PHP_EOL;
        }
    }

    /**
     * Extract waypoint name from other fields.
     *
     * @return string The extracted waypoint
     */
    protected function _wpt_extractor(?SimpleXMLElement $cache): string {
        return $this->string_cleaner($cache->name);
    }

    /**
     * Extract cache name from other fields.
     *
     * @return mixed
     */
    protected function _name_extractor(?SimpleXMLElement $cache): string {
        return $this->string_cleaner($cache->desc);
    }
}
