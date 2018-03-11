<?php

// perform a search śćółżźńóó
//include_once("../../wybierz_jezyk.php"); // choose the user's language
require '../konfig.php';   // config
require '../konfig-local.php';     // config

$kret_helplang = $_POST['helplang'];
// autopoprawione...
$kret_id = $_POST['id'];
// autopoprawione...
$kret_nazwa = $_POST['nazwa'];
// autopoprawione...
$kret_opis = $_POST['opis'];
// autopoprawione...
$kret_owner = $_POST['owner'];
// autopoprawione...
$kret_szablon = $_POST['szablon'];
// autopoprawione...
$kret_tracking = $_POST['tracking'];
// autopoprawione...import_request_variables('p', 'kret_');

// if the szablon is numeric, then we deal with html template

if (is_numeric($kret_szablon)) {
    // gdy nie zadeklarowano języków
    if (!isset($kret_helplang)) {
        $kret_helplang = array('en', 'pl');
    }

    // gdy nie ma angielskiego
    if (!in_array('en', $kret_helplang)) {
        $kret_helplang[] = 'xx';
    }

    foreach ($kret_helplang as $lang) {
        setlocale(LC_MESSAGES, $config_jezyk_encoding[$lang]);
        putenv('LC_ALL='.$config_jezyk_encoding[$lang]); //fox windows only
        setlocale(LC_ALL, $config_jezyk_encoding[$lang]);
        bindtextdomain('messages', BINDTEXTDOMAIN_PATH);
        bind_textdomain_codeset('messages', 'UTF-8');
        $help .= gettext("<strong>User's manual:</strong> <strong>1.</strong> Take this GeoKret. Note down his Tracking Code. <strong>2.</strong> Hide in another cache. <strong>3.</strong> Register the trip at https://geokrety.org/");
        if ($kret_szablon == '3') {
            //add qr-code info
            $help .= '<br />'.gettext('You can also go to the apppropriate page by decoding the QR-code.');
        }
        $help .= '<p></p>';
    }

    include_once SMARTY_DIR.'Smarty.class.php';

    $smarty = new Smarty();
    $smarty->template_dir = './';
    $smarty->compile_dir = '../compile/';
    $smarty->cache_dir = '../cache/';

    $smarty->error_reporting = E_ALL;

    $smarty->assign('help', $help);
    $smarty->assign('szablon', $kret_szablon);
    $smarty->assign('nazwa', stripcslashes($kret_nazwa));
    $smarty->assign('id', $kret_id);
    $smarty->assign('owner', $kret_owner);
    $smarty->assign('tracking', $kret_tracking);
    $smarty->assign('opis', stripcslashes(strip_tags($kret_opis, '<img>')));
    $smarty->assign('szablon_css', "$kret_szablon/label.css");

    $smarty->display($kret_szablon.'/label.html');
}
    // if we have prefix png then we have PNG template

elseif (substr($kret_szablon, 0, 3) == 'png') {
    include_once "$kret_szablon/conf.php";
}
