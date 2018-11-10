<?php

use PHPUnit\Framework\TestCase;

abstract class GKTestCase extends TestCase {
    protected $hasDatabase = false;

    protected $preserveGlobalState = false;
    // protected $runTestInSeparateProcess = TRUE;

    public static function setUpBeforeClass() {
    }

    public function __construct() {
        if (getenv('test_database_host')) {
            $this->hasDatabase = true;
        }
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
}
