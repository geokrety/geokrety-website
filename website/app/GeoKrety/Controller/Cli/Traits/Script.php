<?php

namespace GeoKrety\Controller\Cli\Traits;

use Base;
use DateTime;
use DB\SQL;
use Exception;
use GeoKrety\Model\Scripts;
use GeoKrety\Service\ConsoleWriter;
use function pcntl_async_signals;
use function pcntl_signal;

trait Script {
    protected DateTime $start_datetime;
    protected bool $_skip_saving_final_last_update = false;
    protected SQL $db;
    protected ConsoleWriter $console_writer;
    private string $script_name;

    public function __construct() {
        $this->initScript();
    }

    public function initScript() {
        $this->start_datetime = new DateTime();
        $this->db = Base::instance()->get('DB');
        $this->db->log(false);
        $this->console_writer = new ConsoleWriter();
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
     * Callback used on signal trap.
     */
    public function shutdown() {
        $this->db->rollback();
        $this->unlock();
        echo PHP_EOL.'Exiting…'.PHP_EOL;
        exit();
    }

    /**
     * Start import functions.
     *
     * @throws \Exception
     */
    protected function script_start(?string $func = null) {
        if (!is_null($func)) {
            $this->script_name = $func;
        }
        if (is_null($this->script_name)) {
            throw new Exception('No script name passed');
        }
        $this->lock();
        if ($this->useTransation()) {
            $this->db->begin();
        }
        echo $this->console_writer->sprintf("* \e[0;32mStarting %s script processing at %s\e[0m", $func, $this->start_datetime->format('Y-m-d H:i:s')).PHP_EOL;
        $this->console_writer->flush();
    }

    private function lock() {
        $script_lock = new Scripts();
        $script_lock->load(['name = ?', $this->script_name]);
        try {
            $script_lock->lock($this->script_name);
        } catch (Exception $exception) {
            echo $this->console_writer->sprintf("\e[0;31mE: %s\e[0m", $exception->getMessage()).PHP_EOL;
            exit();
        }
    }

    /**
     * Process end actions.
     */
    protected function script_end(?int $exit = 0) {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }

        if (!$this->_skip_saving_final_last_update and method_exists($this, 'save_last_update')) {
            // Hook to be called on end
            $this->save_last_update();
        }
        $this->unlock();
        echo $this->console_writer->sprintf("* \e[0;%dmEnd script processing: %s\e[0m", ($exit > 0 ? 31 : 32), date('YmdHis')).PHP_EOL;
        exit($exit);
    }

    private function unlock() {
        $script_lock = new Scripts();
        $script_lock->load(['name = ?', $this->script_name]);
        $script_lock->unlock($this->script_name);
    }

    public function useTransation(): bool {
        return true;
    }
}
