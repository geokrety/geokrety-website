<?php

namespace GeoKrety\Controller\Cli;

use DateTime;
use Exception;
use GeoKrety\Model\WaypointOC;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\ConsoleWriter;
use GeoKrety\Service\File;
use PDOException;

class WaypointsImporterGcSu extends WaypointsImporterBase {
    public const GC_SU_API_ENDPOINT = 'https://geocaching.su/rss/geokrety/api.php';
    public const GC_SU_CACHE_DETAIL_URL = 'https://geocaching.su/?pn=101&cid=%s';

    public const SCRIPT_CODE = 'GC_SU';
    protected string $class_name = __CLASS__;

    /**
     * Start gc.su import.
     *
     * @throws Exception
     */
    public function process() {
        echo $this->console_writer->sprintf("** \e[0;32mProcessing gc.su\e[0m").PHP_EOL;
        ob_flush();

        $okapiSync = new WaypointSync();
        $okapiSync->load(['service_id = ?', self::SCRIPT_CODE]);
        $since = '20y';
        $now = new DateTime();
        if ($okapiSync->valid() and !is_null($okapiSync->revision)) {
            $since = sprintf('%dm', ceil(($now->getTimestamp() - $okapiSync->get_last_update_as_datetime()->getTimestamp()) / 60));
        }
        $this->perform_incremental_update($since);
    }

    /**
     * @throws Exception
     */
    private function perform_incremental_update(string $since) {
        echo $this->console_writer->sprintf("*** \e[0;33mRunning import since: %s\e[0m", $since).PHP_EOL;

        $tmp_file = tmpfile();
        $path = stream_get_meta_data($tmp_file)['uri'];
        $nUpdated = 0;
        $nError = 0;

        $url_params = http_build_query([
            'changed' => 1,
            'interval' => $since,
        ]);
        File::download(sprintf('%s?%s', self::GC_SU_API_ENDPOINT, $url_params), $path);
        $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_NOCDATA);

        $caches_count = sizeof($xml->cache);
        if ($caches_count) {
            $console_writer = new ConsoleWriter('Importing cache %7s: %6.2f%% (%s/%d) - %d errors');
            foreach ($xml->cache as $cache) {
                $wpt = new WaypointOC();
                $wpt->load(['waypoint = ?', $cache->code]);
                $wpt->waypoint = $this->string_cleaner($cache->code);
                $wpt->provider = self::SCRIPT_CODE;
                $wpt->name = $this->string_cleaner($cache->name);
                $wpt->lat = number_format(floatval($cache->position['lat']), 5, '.', '');
                $wpt->lon = number_format(floatval($cache->position['lon']), 5, '.', '');
                $wpt->owner = $this->string_cleaner($cache->author);
                $wpt->link = sprintf(self::GC_SU_CACHE_DETAIL_URL, substr($wpt->waypoint, 2, 10));
                $wpt->type = $this->cache_type($cache->code);
                $wpt->status = $this->status_to_id($cache->status, $cache->subtype);
                try {
                    $wpt->save();
                } catch (PDOException $exception) {
                    ++$nError;
                    continue;
                }
                $total = ++$nUpdated + $nError;
                $console_writer->print([$cache->code, $total / $caches_count * 100, $total, $caches_count, $nError]);
            }
            echo PHP_EOL;
        }
    }

    private function cache_type(string $waypoint): string {
        $prefix = substr($waypoint, 0, 2);
        switch ($prefix) {
            case 'MS':
                return 'Multi';
            case 'VI':
                return 'Virtual';
            case 'MV':
                return 'Multi-Virtual';
            case 'LT':
                return 'Mystery';
            case 'LV':
                return 'Virtual Mystery';
            case 'TR':
            default:
                return 'Traditional';
        }
    }

    /**
     * Convert gc.su statuses to oc statuses.
     *
     * @param string      $status  gc.su cache status
     * @param string|null $subtype gc.su Cache subtype
     *
     * @return int|null The cache status
     */
    protected function status_to_id(string $status, ?string $subtype = null): ?int {
        if ($status !== '1') {
            // Cache is not in `good status`
            return 3; // archived
        }
        switch ($subtype) {
            case '1': // Available
                return 1; // active
            case '2': // presumably unavailable
            case '3': // unavailable
                return 2; // temporarily disabled
            default:
                return null;
        }
    }
}
