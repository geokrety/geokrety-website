<?php

use PHPUnit\Framework\TestCase;

abstract class GKTestCase extends TestCase {
    protected $verbose = false;

    protected $hasDatabase = false;

    protected $mapDirectory = 'tests/';
    protected $cacheDirectory = 'tests/__cache/';

    protected $preserveGlobalState = false;
    // protected $runTestInSeparateProcess = TRUE;

    protected $testUtil;

    public static function setUpBeforeClass() {
    }

    public function __construct() {
        if (getenv('test_database_host')) {
            $this->hasDatabase = true;
        }
        $this->testUtil = new TestUtil();
    }

    public function setUp() {
        if (getenv('test_show_method_name') == 'true') {
            fwrite(STDOUT, get_class($this).' :: '.$this->getName()."\n\n");
        }
    }

    public function assumeTestDatabase() {
        if (!$this->hasDatabase) {
            $this->markTestIncomplete('test database needed');
        }
    }

    public function cleanCache() {
        if (file_exists($this->cacheDirectory)) {
            $this->deleteDir($this->cacheDirectory);
        }
        file_exists($this->cacheDirectory) || mkdir($this->cacheDirectory, 0777, true);
    }

    // https://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
    public static function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath.'*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}
