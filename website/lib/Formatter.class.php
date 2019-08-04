<?php

class Formatter {
    private static $_instance = null;
    private $parser = null;
    private $purifier = null;

    private function __construct() {
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Formatter();
        }

        return self::$_instance;
    }

    public static function getParser() {
        $instance = self::getInstance();
        if ($instance->parser === null) {
            $instance->parser = new \Parsedown();
            $instance->parser->setStrictMode(true);
        }
        return $instance->parser;
    }

    public static function getPurifier() {
        $instance = self::getInstance();
        if ($instance->purifier === null) {
            $HTMLPurifierconfig_conf = \HTMLPurifier_Config::createDefault();
            $HTMLPurifierconfig_conf->set('Cache.SerializerPath', TEMP_DIR_HTMLPURIFIER_CACHE);
            $instance->purifier = new \HTMLPurifier($HTMLPurifierconfig_conf);
        }
        return $instance->purifier;
    }

    public static function toText($string) {
        return strip_tags(self::toHtml($string));
    }

    public static function toHtml($string) {
        $html = self::getParser()->text($string);
        return self::getPurifier()->purify($html);
    }

    public function __clone() {
        throw new Exception("Can't clone a singleton");
    }
}
