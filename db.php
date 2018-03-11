<?php

class db
{
    private $mysqli = null;

    public $use_errory = true;
    public $defektoskop_path = 'defektoskop.php';
    public $failed_result_error = '';
    public $failed_result_sev = 100;
    public $failed_result_code = '';
    public $empty_result_accept = 0;
    public $empty_result_error = '';
    public $empty_result_sev = 100;
    public $empty_result_code = '';

    public $selected = null;
    public $matched = null;
    public $affected = null;
    public $info = null;

    public $czas = 0;
    public $overloaded = false;

    private $clear_errors_after_exec = false;

    public function __construct()
    {
        if (!defined('CONFIG_HOST')) {
            include 'templates/konfig.php';
        }
        $this->pconnect();
    }

    public function __destruct()
    {
        //...
    }

    public function __toString()
    {
        return "I'm a db class :)";
    }

    public function get_db_link()
    {
        return $this->mysqli;
    }

    private function pconnect()
    {
        $this->mysqli = new mysqli(constant('CONFIG_HOST'), constant('CONFIG_USERNAME'), constant('CONFIG_PASS'), constant('CONFIG_DB'));
        if (!$this->mysqli) {
            die('DB ERROR: '.$this->mysqli->errno);
        }
        $this->mysqli->set_charset(constant('CONFIG_CHARSET'));
    }

    // this returns a resource or false if query failed
    public function exec($newsql, &$num_rows = null, $empty_result_accept = 0, $error_msg = '', $severity = 100, $code = '')
    {
        if (!empty($error_msg)) {
            $this->set_errors($error_msg, $severity, $code);
        }

        $old = $this->empty_result_accept;
        if ($empty_result_accept) {
            $this->empty_result_accept = $empty_result_accept;
        }
        $result = $this->execute($newsql, $num_rows);
        $this->empty_result_accept = $old;

        return $result;
    }

    // this uses the mysqli_fetch_array function,
    // returns an associative array and a numeric array, or null for error
    // you can do this:
    // $row = $db->exec_fetch_array($sql,$num_rows);
    // if(!$row) { exit; }  //  or if($num_rows<=0) { exit; }
    // list($a, $b, $c) = $row;
    public function exec_fetch_array($newsql, &$num_rows = null, $empty_result_accept = 0, $error_msg = '', $severity = 100, $code = '')
    {
        $result = $this->exec($newsql, $num_rows, $empty_result_accept, $error_msg, $severity, $code);
        if ($num_rows <= 0) {
            return null;
        }
        $row = $result->fetch_array();
        $this->free_result($result);

        return $row;
    }

    // this uses the mysqli_fetch_row function,
    // returns an enumerated array, or null for error
    // you can do this:
    // $row = $db->exec_fetch_array($sql,$num_rows);
    // if(!$row) { exit; }  //  or if($num_rows<=0) { exit; }
    // list($a, $b, $c) = $row;
    public function exec_fetch_row($newsql, &$num_rows = null, $empty_result_accept = 0, $error_msg = '', $severity = 100, $code = '')
    {
        $result = $this->exec($newsql, $num_rows, $empty_result_accept, $error_msg, $severity, $code);
        if ($num_rows <= 0) {
            return null;
        }
        $row = $result->fetch_row();
        $this->free_result($result);

        return $row;
    }

    // returns the first value returned by mysqli_fetch_row or null for error
    // you can do this:
    // $value = $db->exec_scalar($sql);
    public function exec_scalar($newsql, &$num_rows = null, $empty_result_accept = 0, $error_msg = '', $severity = 100, $code = '')
    {
        $row = $this->exec_fetch_row($newsql, $num_rows, $empty_result_accept, $error_msg, $severity, $code);

        return is_null($row) ? null : $row[0];
    }

    // this returns the number of rows (selected or MATCHED*), or -1 for error
    // *) note: for example an UPDATE query may MATCH 10 records but if no values are being changed then AFFECTED rows will be 0; thats why we use matched.
    public function exec_num_rows($newsql, &$num_rows = null, $empty_result_accept = 0, $error_msg = '', $severity = 100, $code = '')
    {
        $result = $this->exec($newsql, $num_rows, $empty_result_accept, $error_msg, $severity, $code);
        $this->free_result($result);

        return $num_rows;
    }

    public function set_errors($errormsg, $severity = 100, $code = '')
    {
        $errormsg = "<b>$errormsg</b>";
        $this->failed_result_error = $errormsg;
        $this->empty_result_error = $errormsg;
        $this->failed_result_sev = $severity;
        $this->empty_result_sev = $severity;
        $this->failed_result_code = $code;
        $this->empty_result_code = $code;
        $this->clear_errors_after_exec = true;
    }

    public function clear_errors()
    {
        $this->failed_result_error = '';
        $this->empty_result_error = '';
        $this->failed_result_sev = 100;
        $this->empty_result_sev = 100;
        $this->failed_result_code = '';
        $this->empty_result_code = '';
        $this->clear_errors_after_exec = false;
    }

    private function clear_counters()
    {
        $this->selected = null;
        $this->matched = null;
        $this->affected = null;
        $this->info = null;
    }

    private function execute($newsql, &$num_rows)
    {
        $this->clear_counters();
        $select = false;
        $update = false;
        $delete = false;
        $insert = false;
        $ret = -1;

        $sql = trim($newsql);
        $sql6 = strtoupper(substr($sql, 0, 6));
        switch ($sql6) {
        case 'SELECT': $select = true;
            break;
        case 'UPDATE': $update = true;
            break;
        case 'DELETE': $delete = true;
            break;
        case 'INSERT': $insert = true;
            break;
        default:
            include_once $this->defektoskop_path;
            $this->send_error_to_db("<b>Nie rozumiem co ty do mnie mowisz!!</b> - $newsql", 100);
            exit("This webpage wants to perform an illegal operation but... don't worry - it won't ;) Instead you'll just see this error page!");
        }

        $czas1 = microtime();

        $result = $this->mysqli->query($sql);
        $this->info = $this->mysqli->info;

        $czas2 = microtime();
        $this->czas = number_format($czas2 - $czas1, 3);

        //$this->send_error_to_db("db timeout: 123", 2, 'db-timeout');
        // if ( (!$this->overloaded) && ($this->czas >= 2) )
        // {
        // $sql2="INSERT INTO `gk-errory` (`uid`, `userid`, `ip` ,`date`, `file` ,`details` ,`severity`)
        // VALUES ('DB-TIMEOUT', '?', '" .$_SERVER['REMOTE_ADDR']. "', '" . date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']) . "', '?', '<b>TIME=$czas_tmp</b> [".mysqli_real_escape_string($sql)."]', '2')";
        // $this->execute_raw($sql2);
        // $this->overloaded = true;
        // }

        if ($result) {
            if ($select) {
                $ret = $this->selected = $result->num_rows;
            } else {
                if ($update) { // probably mached records is more important than affected
                    $this->affected = $this->mysqli->affected_rows;

                    // http://php.net/manual/en/mysqli.affected-rows.php#116152
                    // probably matched records is more important than affected
                    preg_match('/Rows matched: ([0-9]*)/', $this->info, $rows_matched);
                    $ret = $this->matched = $rows_matched[1];
                } else {
                    $ret = $this->affected = $this->mysqli->affected_rows;
                }
            }
        }

        if ($ret <= 0 && $this->use_errory) {
            if ($ret < 0) {
                $error = $this->failed_result_error;
                if (!empty($error)) {
                    $error .= '<br />';
                }
                $error .= $this->info.'<br />Error No '.$this->mysqli->errno.': '.$this->mysqli->error;
                $error .= "<br />SQL FAILED: [$sql]";
                $this->send_error_to_db($error, $this->failed_result_sev, $this->failed_result_code);
            } elseif (!$this->empty_result_accept) {
                $error = $this->empty_result_error;
                if (!empty($error)) {
                    $error .= '<br/>';
                }
                $error .= "SQL RETURNED 0 ROWS: [$sql] mysqli_info: [".$this->info.']';
                $this->send_error_to_db($error, $this->empty_result_sev, $this->empty_result_code);
            }
        }

        $num_rows = $ret;

        if ($this->clear_errors_after_exec) {
            $this->clear_errors();
        }

        return $result;
    }

    private function execute_raw($sql)
    {
        $mylink = new mysqli(constant('CONFIG_HOST'), constant('CONFIG_USERNAME'), constant('CONFIG_PASS'), constant('CONFIG_DB'));
        if (!$mylink) {
            die('execute_raw DB ERROR: '.$mylink->errno);
        }
        $mylink->query($sql);
        $mylink->close();
    }

    private function send_error_to_db($details, $severity = 0, $error_uid = '')
    {
        include_once $this->defektoskop_path;
        errory_add($details, $severity, $error_uid);
    }

    private function free_result($result)
    {
        if ($result instanceof mysqli_result) {
            $result->free_result();
        }
    }
}
