<?php

namespace GeoKrety\Controller\Cli;

use Base;
use DateTime;
use Exception;
use GeoKrety\Model\Scripts;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\File;
use GeoKrety\Service\HTMLPurifier;
use function pcntl_signal;
use SimpleXMLElement;

abstract class WaypointsImporterBase {
    protected DateTime $start_datetime;
    protected \HTMLPurifier $purifier;

    public function __construct() {
        $this->start_datetime = new DateTime();
        $this->purifier = HTMLPurifier::getPurifier();

        // Disable database log profiler - it explode memory in big imports
        Base::instance()->get('DB')->log(false);
        $this->trap_sigint();
        $this->lock();
    }

    /**
     * Enable signal trapping.
     */
    private function trap_sigint() {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, [$this, 'shutdown']);       // Catch SIGINT, run shutdown()
    }

    /**
     * Lock the script usage. Prevent running multiple times.
     */
    private function lock() {
        $script_lock = new Scripts();
        $script_lock->load(['name = ?', static::SCRIPT_NAME]);
        try {
            $script_lock->lock(static::SCRIPT_NAME);
        } catch (Exception $exception) {
            echo sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
            exit();
        }
    }

    /**
     * Start the import process.
     */
    abstract public function process();

    /**
     * Start import functions.
     */
    protected function start() {
        echo sprintf("* \e[0;32mStarting Waypoint synchronization: %s\e[0m", $this->start_datetime->format('YmdHis')).PHP_EOL;
    }

    /**
     * Process end actions.
     */
    protected function end() {
        $this->unlock();
        echo sprintf("* \e[0;32mEnd Waypoint synchronization: %s\e[0m", date('YmdHis')).PHP_EOL;
    }

    /**
     * Unlock the script.
     */
    private function unlock() {
        $script_lock = new Scripts();
        $script_lock->load(['name = ?', static::SCRIPT_NAME]);
        $script_lock->unlock(static::SCRIPT_NAME);
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
     * Store last script update.
     *
     * @param string|null $service  The service code
     * @param int|null    $revision The eventual revision to store
     */
    protected function save_last_update(?string $service = null, ?int $revision = null) {
        $svc = $service ?? static::SCRIPT_CODE;

        $okapiSync = new WaypointSync();
        $okapiSync->load(['service_id = ?', $svc]);
        $okapiSync->service_id = $svc;
        $okapiSync->revision = $revision;
        $okapiSync->last_update = $this->start_datetime->format(GK_DB_DATETIME_FORMAT_AS_INT);
        $okapiSync->save();
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
        // TODO
        return trim(html_entity_decode($this->purifier->purify($text)));
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

        return simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_NOCDATA);
    }

    /**
     * Callback used on signal trap.
     */
    private function shutdown() {
        $this->unlock();
        echo PHP_EOL.'Exiting…'.PHP_EOL;                 // New line
        exit();                                                // Clean quit
    }
}
