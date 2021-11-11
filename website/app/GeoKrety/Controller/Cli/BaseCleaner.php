<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Service\HTMLPurifier;

define('PER_PAGE', 1000);

abstract class BaseCleaner {
    use Script;

    protected int $total;
    protected int $counter;
    protected \HTMLPurifier $purifier;
    private int $counterFixed;
    /**
     * @var float|int
     */
    protected $percentProcessed;
    /**
     * @var float|int
     */
    protected $percentErrors;
    protected string $class_name = __CLASS__;

    public function __construct() {
        $this->initScript();
        $this->purifier = HTMLPurifier::getPurifier();
        $this->console_writer->setPattern($this->getConsoleWriterPattern());
    }

    protected function getConsoleWriterPattern(): string {
        return 'Processing records: %6.2f%% (%s/%d - fixed: %6.2f%%)';
    }

    /**
     * @throws \Exception
     */
    public function processAll(Base $f3) {
        $this->script_start($this->class_name.'::'.__FUNCTION__);
        $filter = $this->filterHook();
        $model = $this->getModel();
        $this->total = $model->count($filter);
        if (!$this->total) {
            echo $this->console_writer->sprintf("\e[0;32mNo %s found\e[0m", $this->getModelName()).PHP_EOL;
            $this->script_end();
        }
        echo $this->console_writer->sprintf('%d %s to proceed', $this->total, $this->getModelName()).PHP_EOL;

        // Paginate the table resultset as it may blow ram!
        $start_page = 0;
        $total_pages = ceil($this->total / PER_PAGE);
        $this->counter = ($start_page) * PER_PAGE;
        $this->counterFixed = 0;
        Base::instance()->get('DB')->log(false);

        for ($i = $start_page; $i < $total_pages; ++$i) {
            $subset = $model->paginate($i, PER_PAGE, $filter, $this->orderHook());
            foreach ($subset['subset'] as $object) {
                $this->process($object);
            }
        }
        $this->console_writer->flush();
        echo $this->console_writer->sprintf(PHP_EOL."\e[0;32mRecomputed %d %s. %d Fixed (%0.2f%%)\e[0m", $this->counter, $this->getModelName(), $this->counterFixed, $this->percentErrors).PHP_EOL;
        $this->script_end();
    }

    abstract protected function getModelName(): string;

    abstract protected function filterHook();

    abstract protected function getModel(): \GeoKrety\Model\Base;

    protected function orderHook(): array {
        return ['order' => 'updated_on_datetime ASC'];
    }

    abstract protected function process($object): void;

    public function processById(Base $f3) {
        $this->script_start($this->class_name.'::'.__FUNCTION__);
        $model = $this->getModel();
        $model->load(['id = ?', $this->getParamId($f3)]);
        if ($model->dry()) {
            echo $this->console_writer->sprintf("\e[0;32mNo such %s found\e[0m", $this->getModelName()).PHP_EOL;

            return;
        }
        $this->total = 1;
        $this->process($model);
        $this->script_end();
    }

    abstract protected function getParamId(Base $f3): int;

    protected function processResult(bool $fixed): void {
        ++$this->counter;
        $this->counterFixed += $fixed;
        $this->percentProcessed = $this->counter / $this->total * 100;
        $this->percentErrors = $this->counterFixed / $this->total * 100;
    }

    protected function print(): void {
        $this->console_writer->print([$this->percentProcessed, $this->counter, $this->total, $this->percentErrors]);
    }
}
