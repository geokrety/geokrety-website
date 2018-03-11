<?php

require_once '../../konfig.php';    // config

$aResponse['error'] = false;
$aResponse['message'] = '';

if (isset($_POST['action'])) {
    if (htmlentities($_POST['action'], ENT_QUOTES, 'UTF-8') == 'rating') {
        $id = intval($_POST['idBox']);
        $userid = intval($_POST['userid']);
        $rate = floatval($_POST['rate']);
        $controlsum = strval($_POST['controlsum']);
        $lang = strval($_POST['lang']);
        $ratingSha = sha1(date('ynj').$userid.$config['jrating_token']);  // for proofing voting userid
        if ($controlsum == $ratingSha) {
            @setlocale(LC_MESSAGES, $lang);

            include '../../konfig.php';    // config
            $link = DBConnect();
            $sql = "INSERT INTO `gk-geokrety-rating` (`id`, `userid`, `rate`) VALUES ('$id', '$userid', '$rate');";
            $result = mysqli_query($link, $sql);

            $sql = "SELECT count(`rate`), avg(`rate`)  FROM `gk-geokrety-rating` WHERE `id`=$id LIMIT 1";
            $result = mysqli_query($link, $sql);
            $row = mysqli_fetch_row($result);
            list($ratingCount, $ratingAvg) = $row;

            $success = true;
        } else {
            $success = false;
        }

        // json datas send to the js file
        if ($success) {
            //$aResponse['message'] = _('GeoKret successfully rated :)');
            $aResponse['message'] = '<img src="https://cdn.geokrety.org/images/icons/ok.png" width="16" height="16" alt="OK" />';

            echo json_encode($aResponse);
        } else {
            $aResponse['error'] = true;
            $aResponse['message'] = 'Error (controlsum) :(';

            echo json_encode($aResponse);
        }
    } else {
        $aResponse['error'] = true;
        //$aResponse['message'] = '"action" post data not equal to \'rating\'';
        $aResponse['message'] = 'Error :(';

        echo json_encode($aResponse);
    }
} else {
    $aResponse['error'] = true;
    //$aResponse['message'] = '$_POST[\'action\'] not found';
    $aResponse['message'] = 'Error :(';

    echo json_encode($aResponse);
}
