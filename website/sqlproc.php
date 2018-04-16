<?php


class sqlproc
{
    public $sql;
    public $use_errory = true;
    public $defektoskop_path = 'defektoskop.php';
    public $failed_error = '';
    public $failed_sev = 100;
    public $ret0rows_enabled = 1;
    public $ret0rows_error = '';
    public $ret0rows_sev = 100;

    private $clear_errors_after_exec = false;

    public function exec($newsql, &$num_rows = null)
    {
        return $this->execute($newsql, $num_rows);
    }

    public function exec_ignore0($newsql, &$num_rows = null)
    {
        $old = $this->ret0rows_enabled;
        $this->ret0rows_enabled = 0;

        return $this->execute($newsql, $num_rows);
        $this->ret0rows_enabled = $old;
    }

    public function exec_fetch_array($newsql, &$num_rows = null)
    {
        $result = $this->execute($newsql, $num_rows);
        if ($num_rows > 0) {
            $row = mysql_fetch_array($result);
            mysql_free_result($result);

            return $row;
        } else {
            return null;
        }
    }

    public function exec_num_rows($newsql)
    {
        $this->execute($newsql, $num_rows);

        return $num_rows;
    }

    public function set_errors($errormsg, $newseverity = 100)
    {
        $errormsg = "<b>$errormsg</b>";
        $this->failed_error = $errormsg;
        $this->ret0rows_error = $errormsg;
        $this->failed_sev = $newseverity;
        $this->ret0rows_sev = $newseverity;
        $this->clear_errors_after_exec = true;
    }

    public function clear_errors()
    {
        $this->failed_error = '';
        $this->ret0rows_error = '';
        $this->failed_sev = 100;
        $this->ret0rows_sev = 100;
        $this->clear_errors_after_exec = false;
    }

    private function execute($newsql, &$num_rows)
    {
        $sql = trim($newsql);
        $sql6 = strtoupper(substr($sql, 0, 6));
        if ($sql6 == 'SELECT') {
            $select = 1;
        } else {
            if ($sql6 == 'UPDATE' || $sql6 == 'DELETE' || $sql6 == 'INSERT') {
                $select = 0;
            } else {
                include_once $this->defektoskop_path;
                errory_add("<b>Nie rozumiem co ty do mnie mowisz!!</b> - $newsql", 100);
                exit("This webpage wants to perform an illegal operation but... don't worry - it won't ;) Instead you'll just see this error page!");
            }
        }

        $result = mysql_query($sql);
        if (!$result) {
            $ret = -1;
        } else {
            if ($select) {
                $ret = mysql_num_rows($result);
            } else {
                $ret = mysql_affected_rows();
            }
        }

        if (($ret <= 0) && $this->use_errory) {
            include_once $this->defektoskop_path;
            if ($ret < 0) {
                $error = $this->failed_error;
                if (!empty($error)) {
                    $error .= '<br />';
                }
                $error .= "SQL FAILED: $sql";
                $error .= '<br />'.mysql_errno().': '.mysql_error();
                $severity = $this->failed_sev;
                errory_add($error, $severity);
            } elseif ($this->ret0rows_enabled) {
                $error = $this->ret0rows_error;
                if (!empty($error)) {
                    $error .= '<br/>';
                }
                $error .= "SQL RETURNED 0 ROWS: $sql";
                $severity = $this->ret0rows_sev;
                errory_add($error, $severity);
            }
        }

        $num_rows = $ret;

        if ($this->clear_errors_after_exec) {
            $this->clear_errors();
        }

        return $result;
    }
}
