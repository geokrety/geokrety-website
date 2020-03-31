<?php

namespace GeoKrety\Service;

class ConsoleWriter {
    private $pattern;
    private $lastLen;

    private $timer;
    private $status = '';

    public function __construct(string $pattern, ?array $values = null) {
        $this->pattern = $pattern.' - %0.1fs - load: %s';

        if (!is_null($values)) {
            $this->print($values);
        }

        $this->timer = microtime(true);
    }

    public function print(array $values) {
        if ($this->lastLen) {
            echo "\033[{$this->lastLen}D";
        }

        $values[] = microtime(true) - $this->timer;
        list($values[], $sleep) = $this->loadAvgSleeper();
        $this->_print($values);
        if ($sleep) {
            echo "\n";
            sleep($sleep);
        }
    }

    public function _print(array &$values) {
        $newString = vsprintf($this->pattern, $values);
        $this->lastLen = strlen($newString);
        echo $newString;
        ob_flush(); // TODO: Required for PictureImporter, but seems to fail on other case
        flush();
    }

    public function loadAvgSleeper() {
        $load = sys_getloadavg()[0];
        $loadStr = sprintf('%0.2f', $load);
        if ($load > 3.00) {
            return ["\e[41m{$loadStr}\e[0m", 5];
        }
        return ["\e[32m{$loadStr}\e[0m", 0];
    }
}
