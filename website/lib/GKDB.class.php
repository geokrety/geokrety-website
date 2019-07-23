<?php

class GKDB {
    private static $connectCount = 0;
    private static $_instance = null;

    private $link = null;

    private function __construct() {
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new GKDB();
        }

        return self::$_instance;
    }

    public static function getLink() {
        return self::getInstance()->connect();
    }

    public static function getConnectCount() {
        return self::$connectCount;
    }

    public function connect() {
        if ($this->link !== null) {
            try {
                if (@mysqli_ping($this->link)) {
                    return $this->link;
                }
            } catch (Exception $exc) {
            }
        }
        // DEBUG // echo 'connect '.CONFIG_USERNAME.'@'.CONFIG_HOST.' using '.CONFIG_PASS;
        try {
            $this->link = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASS);
            if (!$this->link) {// lets retry
                $this->link = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASS);
                if (!$this->link) {
                    throw new Exception('Unable to join database server');
                }
            }
            $db_select = mysqli_select_db($this->link, CONFIG_DB);
            if (!$db_select) {
                throw new Exception('Unknown database "'.CONFIG_DB.'" : '.mysqli_errno($this->link));
            }
            $this->link->set_charset(CONFIG_CHARSET);
            $this->link->query("SET time_zone = '".CONFIG_TIMEZONE."'");
            ++self::$connectCount;

            return $this->link;
        } catch (Exception $exc) {
            $errorId = uniqid('GKIE_');
            $errorMessage = 'DB ERROR '.$errorId.' - '.$exc->getMessage();
            error_log($errorMessage);
            error_log($exc);
            $this->link = null; // do not reuse link on error
            throw new Exception($errorMessage);
        }
    }

    public function close() {
        if (!isset($this->link)) {
            return;
        }
        mysqli_close($this->link);
        unset($this->link);
    }

    public static function prepareBindExecute(string &$action, string &$sql, string &$bindParams = null, array &$bindValues = null) {
        $link = self::getLink();
        if (!($stmt = $link->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$link->errno.') '.$link->error);
        }
        if (!is_null($bindValues) && !$stmt->bind_param($bindParams, ...$bindValues)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        return $stmt;
    }

    public function __clone() {
        throw new Exception("Can't clone a singleton");
    }
}
