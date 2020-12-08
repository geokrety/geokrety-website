<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\Scripts;
use GeoKrety\Service\ConsoleWriter;
use GeoKrety\Service\HTMLPurifier;

define('PER_PAGE', 1000);

// TODO: disable triggers before processing

abstract class BaseCleaner {
    /**
     * @var int
     */
    protected $total;
    /**
     * @var int
     */
    protected $counter;
    /**
     * @var int
     */
    private $counterFixed;
    /**
     * @var float|int
     */
    protected $percentProcessed;
    /**
     * @var float|int
     */
    protected $percentErrors;
    /**
     * @var float|string
     */
    private $timeSpent;
    /**
     * @var \HTMLPurifier
     */
    protected $purifier;
    /**
     * @var Scripts
     */
    protected $script;
    /**
     * @var ConsoleWriter
     */
    protected $consoleWriter;

    public function __construct() {
        $this->purifier = HTMLPurifier::getPurifier();
        $this->consoleWriter = new ConsoleWriter($this->getConsoleWriterPattern());
    }

    abstract protected function filterHook();

    protected function _filterByUpdatedOnDatetime() {
        return ['updated_on_datetime > ?', $this->script->last_run_datetime->format(GK_DB_DATETIME_FORMAT)];
    }

    protected function orderHook() {
        return ['order' => 'updated_on_datetime ASC'];
    }

    protected function getConsoleWriterPattern() {
        return 'Processing records: %6.2f%% (%s/%d - fixed: %6.2f%%)';
    }

    public function processAll(Base $f3) {
        $this->script = new Scripts();
        $this->script->load(['name = ?', $this->getScriptName()]);
        $filter = $this->filterHook();
        if ($this->script->dry()) {
            $this->script->name = $this->getScriptName();
            $this->script->last_run_datetime = null;
            $this->script->last_page = 0;
            $this->script->save();
        } else {
            $filter = $this->filterHook();
        }

        if ($f3->exists('GET.restart')) {
            $this->script->last_page = $f3->get('GET.restart');
            echo 'Restart at page:'.$this->script->last_page.PHP_EOL;
        }

        $model = $this->getModel();

        $this->total = $model->count($filter);
        if (!$this->total) {
            echo sprintf("\e[0;32mNo %s found\e[0m", $this->getModelName()).PHP_EOL;

            return;
        }
        echo sprintf('%d %s to proceed', $this->total, $this->getModelName()).PHP_EOL;

        // Paginate the table resultset as it may blow ram!
        $start_page = $this->script->last_page;
        $total_pages = ceil($this->total / PER_PAGE);
        $this->counter = ($start_page) * PER_PAGE;
        $this->counterFixed = 0;
        \Base::instance()->get('DB')->log(false);

        for ($i = $start_page; $i < $total_pages; ++$i) {
            $timeBefore = microtime(true);
            $subset = $model->paginate($i, PER_PAGE, $filter, $this->orderHook());
            foreach ($subset['subset'] as $object) {
                $this->process($object);
                $this->script->last_run_datetime = $object->updated_on_datetime->format(GK_DB_DATETIME_FORMAT);
            }
            $this->script->last_page = $i;
            $this->script->touch('last_run_datetime');
            $this->script->save();
            $this->timeSpent = microtime(true) - $timeBefore;
        }
        echo sprintf(PHP_EOL."\e[0;32mRecomputed %d %s. %d Fixed (%0.2f%%)\e[0m", $this->counter, $this->getModelName(), $this->counterFixed, $this->percentErrors).PHP_EOL;
    }

    protected function getScriptName(): string {
        return sprintf('%s_%s', get_class($this), $this->getModelName());
    }

    abstract protected function getModelName(): string;

    abstract protected function getModel(): \GeoKrety\Model\Base;

    abstract protected function process(&$object): void;

    public function processById(Base $f3) {
        $model = $this->getModel();
        $model->load(['id = ?', $this->getParamId($f3)]);
        if ($model->dry()) {
            echo sprintf("\e[0;32mNo such %s found\e[0m", $this->getModelName()).PHP_EOL;

            return;
        }
        $this->total = 1;
        $this->process($model);
    }

    abstract protected function getParamId(Base $f3): int;

    protected function processResult(int $id, bool $fixed): void {
        ++$this->counter;
        $this->counterFixed += $fixed;
        $this->percentProcessed = $this->counter / $this->total * 100;
        $this->percentErrors = $this->counterFixed / $this->total * 100;
    }

    protected function print(): void {
        $this->consoleWriter->print([$this->percentProcessed, $this->counter, $this->total, $this->percentErrors]);
    }
}
