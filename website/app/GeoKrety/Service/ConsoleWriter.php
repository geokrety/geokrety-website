<?php

namespace GeoKrety\Service;

class ConsoleWriter {
    private string $pattern;
    private int $lastLen = 0;
    private float $timer;
    private string $last_message;
    private bool $is_interactive_shell;

    public function __construct(string $pattern = '%s', ?array $values = null) {
        $this->setPattern($pattern);

        if (!is_null($values)) {
            $this->print($values);
        }

        $this->timer = microtime(true);
        $this->setIsInteractive();
    }

    public function setPattern(string $pattern): void {
        $this->pattern = $pattern.' - %0.2fs - load: %s';
    }

    protected function setIsInteractive(): void {
        $this->is_interactive_shell = (defined('STDOUT') and posix_isatty(STDOUT));
    }

    public function IsInteractive(): bool {
        return $this->is_interactive_shell;
    }

    /**
     * A php sprintf() wrapper which removes ANSI color escape sequence when running in non-interactive shell.
     *
     * @param string           $format    strintf format string
     * @param string|int|float ...$values sprintf values
     *
     * @return string Ansi color escaped string
     */
    public function sprintf(string $format, ...$values): string {
        $str = sprintf($format, ...$values);

        return $this->escapeAnsiColors($str);
    }

    public function escapeAnsiColors(string $str): string {
        if ($this->IsInteractive()) {
            return $str;
        }

        return preg_replace('/\x1B\[[^m]*?m/', '', $str);
    }

    public function flush(): void {
        if (isset($this->last_message)) {
            echo $this->escapeAnsiColors($this->last_message);
        }
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        $this->last_message = '';
    }

    public function print(array $values, bool $line_break = false, bool $flush = false) {
        $this->last_message = '';
        if ($this->IsInteractive() and $this->lastLen) {
            $this->last_message .= "\033[{$this->lastLen}D";
        }

        $values[] = microtime(true) - $this->timer;
        list($values[], $sleep) = $this->loadAvgSleeper();
        $this->_print($values);
        if ($sleep) {
            $this->last_message .= PHP_EOL;
            sleep($sleep);
        } elseif ($line_break) {
            $this->last_message .= PHP_EOL;
        }

        if ($this->IsInteractive() or $flush) {
            $this->flush();
        }
    }

    public function _print(array $values) {
        $newString = vsprintf($this->pattern, $values);
        $lenBefore = $this->lastLen;
        $this->lastLen = strlen($newString);
        $this->last_message .= $newString;
        $pad = $lenBefore - $this->lastLen;
        if ($this->IsInteractive() and $pad > 0) {
            $this->last_message .= str_repeat(' ', $pad);
            $this->last_message .= "\033[{$pad}D";
        }
    }

    public function loadAvgSleeper(): array {
        $load = sys_getloadavg()[0];
        $loadStr = sprintf('%0.2f', $load);
        if ($load > 6.00) {
            return ["\e[41m$loadStr\e[0m", 5];
        }

        return ["\e[32m$loadStr\e[0m", 0];
    }
}
