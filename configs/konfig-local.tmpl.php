<?php

// Site url
$config['adres'] = 'http://localhost/';

// Password hashing
// Crypt alorythms https://en.wikipedia.org/wiki/Crypt_(C)#Key_derivation_functions_supported_by_crypt
$config['sol'] = '$5$xxx'; # crypt() hash
$config['sol2'] = 'xxx'; # some random string

// Api2login hashes
$config['md5_string1'] = 'xxx'; # hex chars
$config['md5_string2'] = 'xxx'; # hex chars

// Cryptographic vectors
define('SWISTAK_KEY', 'xxx'); # some random string
define('SWISTAK_IV32', 'xxx'); # 32 hex chars

// Create news password
$config['news_password'] = 'xxx';

// jRating access token
$config['jrating_token'] = 'xxx';

// admin users
$config['superusers'] = array('1', '6262', '26422');

// export day limit bypass
$kocham_kaczynskiego = 'xxx';

// Google map Api key
$GOOGLE_MAP_KEY = '';

// Email gateway
$config['pop_hostname'] = 'pop.gmail.com';
$config['pop_port'] = 995;
$config['pop_tls'] = True;
$config['pop_username'] = 'xxx';
$config['pop_password'] = 'xxx';

// Sentry integration
$config['sentry_dsn'] = 'https://xx:yyy@zzz/1';
$config['sentry_env'] = 'development';

// Piwik conf
$config['piwik_url'] = '';
$config['piwik_site_id'] = '';
$config['piwik_token'] ='';
