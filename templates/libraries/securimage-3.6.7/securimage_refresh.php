<?php


require_once '../../konfig.php';
require_once 'securimage.php';

$captcha_options = array('database_driver' => Securimage::SI_DRIVER_MYSQL,
                 'database_host' => CONFIG_HOST,
                 'database_user' => CONFIG_USERNAME,
                 'database_pass' => CONFIG_PASS,
                 'database_name' => CONFIG_DB,
                 'no_session' => true, );

$captcha = Securimage::getCaptchaId(true, $captcha_options);
$data    = array('captchaId' => $captcha);

echo json_encode($data);
exit;
