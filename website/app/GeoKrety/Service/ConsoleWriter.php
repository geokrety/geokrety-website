<?php

namespace GeoKrety\Service;

class ConsoleWriter {
    private string $pattern;
    private int $lastLen = 0;
    private float $timer;

    public function __construct(string $pattern = '%s', ?array $values = null) {
        $this->pattern = $pattern.' - %0.2fs - load: %s';

        if (!is_null($values)) {
            $this->print($values);
        }

        $this->timer = microtime(true);
    }

    public function setPattern(string $pattern): void {
        $this->pattern = $pattern;
    }

    public function print(array $values, bool $line_break = false) {
        if ($this->lastLen) {
            echo "\033[{$this->lastLen}D";
        }

        $values[] = microtime(true) - $this->timer;
        list($values[], $sleep) = $this->loadAvgSleeper();
        $this->_print($values);
        if ($sleep) {
            echo "\n";
            sleep($sleep);
        } elseif ($line_break) {
            echo "\n";
        }
    }

    public function _print(array $values) {
        $newString = vsprintf($this->pattern, $values); // no mb_vsprintf() exists
        $lenBefore = $this->lastLen;
        $this->lastLen = mb_strlen($newString, 'UTF-8');
        echo $newString;
        $pad = $lenBefore - $this->lastLen;
        if ($pad > 0) {
            echo str_repeat(' ', $pad);
            echo "\033[{$pad}D";
        }
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    public function loadAvgSleeper(): array {
        $load = sys_getloadavg()[0];
        $loadStr = sprintf('%0.2f', $load);
        if ($load > 8.00) {
            return ["\e[41m$loadStr\e[0m", 5];
        }

        return ["\e[32m$loadStr\e[0m", 0];
    }
}
