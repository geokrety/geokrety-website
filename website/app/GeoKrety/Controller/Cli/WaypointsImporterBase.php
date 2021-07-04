<?php

namespace GeoKrety\Controller\Cli;

use Exception;
use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Email\CronError;
use GeoKrety\Model\WaypointOC;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\File;
use GeoKrety\Service\HTMLPurifier;
use GeoKrety\Service\Metrics;
use SimpleXMLElement;

abstract class WaypointsImporterBase {
    use Script;
    protected \HTMLPurifier $purifier;
    protected bool $has_error = false;
    protected ?string $error = null;

    public function __construct() {
        $this->initScript();
        $this->purifier = HTMLPurifier::getPurifier();
    }

    /**
     * Start the import process.
     *
     * @throws Exception
     */
    public function run() {
        $this->start($this->class_name.'::'.__FUNCTION__);
        try {
            $this->process();
        } catch (Exception $exception) {
            $this->has_error = true;
            $this->db->rollback();
            $this->error = $exception->getMessage();
            echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
            echo $exception->getTraceAsString().PHP_EOL;
            $this->end();
            throw $exception;
        }
        $this->end();
    }

    /**
     * The real work process.
     *
     * @throws Exception
     */
    abstract protected function process();

    /**
     * Store last script update.
     *
     * @param string|null $service  The service code
     * @param int|null    $revision The eventual revision to store
     *
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    protected function save_last_update(?string $service = null, ?int $revision = null) {
        $svc = $service ?? static::SCRIPT_CODE;

        $wpt = new WaypointOC();
        $wpt_count = $wpt->count(['provider = ?', $svc]);

        $okapiSync = new WaypointSync();
        $okapiSync->load(['service_id = ?', $svc]);
        $okapiSync->service_id = $svc;
        $okapiSync->revision = $revision ?? $okapiSync->revision;
        $okapiSync->updated_on_datetime = $this->start_datetime->format(GK_DB_DATETIME_FORMAT);
        if (!is_null($this->error)) {
            ++$okapiSync->error_count;
            $okapiSync->last_error = $this->error;
            $okapiSync->last_error_datetime = $this->start_datetime->format(GK_DB_DATETIME_FORMAT);
            $mail = new CronError();
            $mail->sendPartnerFatal($service, $this->error);
        } else {
            $okapiSync->wpt_count = $wpt_count;
            $okapiSync->last_success_datetime = $this->start_datetime->format(GK_DB_DATETIME_FORMAT);
        }
        Metrics::getOrRegisterGauge('waypoint_sync_status', 'Waypoint sync cron status', ['provider'])
            ->set(is_null($this->error), [$svc]);
        $okapiSync->save();
        $this->error = null;
    }

    /**
     * Convert statuses strings to oc statuses.
     *
     * @param string      $status  Cache status
     * @param string|null $subtype Cache subtype
     *
     * @return int|null The cache status
     */
    protected function status_to_id(string $status, ?string $subtype = null): ?int {
        return $status ?: 1;
    }

    /**
     * Clean input text and decode html entities.
     *
     * @param string|null $text The string to clean
     *
     * @return string|null Cleaned string
     */
    protected function string_cleaner(?string $text = null): ?string {
        if (is_null($text)) {
            return null;
        }

        return trim(html_entity_decode($this->purifier->purify($text)));
    }

    /**
     * Clean and url.
     *
     * @param string|null $text The url to clean
     *
     * @return string|null Cleaned url
     */
    protected function url_cleaner(?string $text = null): ?string {
        if (is_null($text)) {
            return null;
        }

        return trim(html_entity_decode($this->purifier->purify($text)));
    }

    /**
     * Download and parse xml.
     *
     * @param $url string The url to download from
     *
     * @return SimpleXMLElement The parsed xml
     *
     * @throws Exception
     */
    protected function download_xml(string $url): SimpleXMLElement {
        $tmp_file = tmpfile();
        $path = stream_get_meta_data($tmp_file)['uri'];
        File::download($url, $path);

        return simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_NOCDATA);
    }
}
