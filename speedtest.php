<?php

// script execution time test

/*
include('speedtest.php');
$st=new SpeedTest;
...
echo $st->stop_show();
*/

class speedtest
{
    private $start_time;
    private $end_time;

    public function SpeedTest()
    {
        $this->end_time = 0;
        $this->start_time = $this->getTimestamp();
    }

    private function getTimestamp()
    {
        $now = gettimeofday();

        return $now['sec'] + ($now['usec'] / 1000000);
    }

    public function start() //called automatically at creation as well
    {
        $this->start_time = $this->getTimestamp();
    }

    public function stop()
    {
        $this->end_time = $this->getTimestamp();
    }

    public function show()
    {
        return number_format(($this->end_time) - ($this->start_time), 5);
    }

    public function stop_show()
    {
        $this->end_time = $this->getTimestamp();

        return number_format(($this->end_time) - ($this->start_time), 5);
    }

    public function stop_show_start()
    {
        $this->end_time = $this->getTimestamp();
        $tmp = number_format(($this->end_time) - ($this->start_time), 5);
        $this->start_time = $this->getTimestamp();

        return $tmp;
    }
}
