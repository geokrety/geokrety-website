<?php

namespace GeoKrety\Controller\Cli;

use Base;
use DateTime;
use Exception;
use GeoKrety\Model\Scripts;
use GeoKrety\Model\WaypointOC;
use GeoKrety\Model\WaypointSync;
use GeoKrety\Service\File;
use GeoKrety\Service\HTMLPurifier;
use function pcntl_async_signals;
use function pcntl_signal;
use SimpleXMLElement;

abstract class WaypointsImporterBase {
    protected DateTime $start_datetime;
    protected \HTMLPurifier $purifier;
    protected bool $has_error = false;
    protected ?string $error = null;
    protected bool $_skip_saving_final_last_update = false;
    /**
     * @var array|array[]|mixed|null
     */
    protected $db;

    public function __construct() {
        $this->start_datetime = new DateTime();
        $this->purifier = HTMLPurifier::getPurifier();
        $this->db = Base::instance()->get('DB');

        // Disable database log profiler - it explode memory in big imports
        Base::instance()->get('DB')->log(false);
        $this->trap_sigint();
    }

    /**
     * Enable signal trapping.
     */
    private function trap_sigint() {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, [$this, 'shutdown']);       // Catch SIGINT, run shutdown()
    }

    /**
     * Start the import process.
     *
     * @throws Exception
     */
    public function run() {
        $this->start();
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
     * Start import functions.
     */
    protected function start() {
        $this->lock();
        $this->db->begin();
        echo sprintf("* \e[0;32mStarting %s Waypoint synchronization at %s\e[0m", static::SCRIPT_CODE, $this->start_datetime->format('Y-m-d H:i:s')).PHP_EOL;
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
     * The real work process.
     *
     * @throws Exception
     */
    abstract protected function process();

    /**
     * Process end actions.
     */
    protected function end() {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }

        if (!$this->_skip_saving_final_last_update) {
            $this->save_last_update();
        }
        $this->unlock();
        echo sprintf("* \e[0;32mEnd Waypoint synchronization: %s\e[0m", date('YmdHis')).PHP_EOL;
    }

    /**
     * Store last script update.
     *
     * @param string|null $service  The service code
     * @param int|null    $revision The eventual revision to store
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
        } else {
            $okapiSync->wpt_count = $wpt_count;
            $okapiSync->last_success_datetime = $this->start_datetime->format(GK_DB_DATETIME_FORMAT);
        }
        $okapiSync->save();
        $this->error = null;
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
     * Callback used on signal trap.
     */
    public function shutdown() {
        $this->db->rollback();
        $this->unlock();
        echo PHP_EOL.'Exiting…'.PHP_EOL;
        exit();
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
        // TODO
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
