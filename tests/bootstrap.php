<?php
 include './vendor/autoload.php';

 $testConfig = dirname(__FILE__).DIRECTORY_SEPARATOR.'config';
 putenv("website_config_directory=$testConfig");

 $dbFile = $testConfig.DIRECTORY_SEPARATOR.'konfig-local.php';
 if (!file_exists($dbFile)) {
     echo "\n bootstrap - config:$testConfig\n";
     echo ' XXX Test config expected '.$dbFile."\n";
     echo "     please run generateTestConfig.sh\n";
     die;
 }
 require_once 'website/templates/konfig.php';
