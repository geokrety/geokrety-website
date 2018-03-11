<?php

function zlicz_droge($ruch_id)
{
    // calculate distance between this and previous location

    $link = DBConnect();
    $result = mysqli_query($link, "SELECT `id`, `data` FROM `gk-ruchy` WHERE `ruch_id`='$ruch_id' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($id, $data) = $row;
    //print_r($row);

    $result = mysqli_query($link, "SELECT `lat`, `lon` FROM `gk-ruchy` WHERE `id`='$id' AND `data`<='$data' AND (`logtype`='0' OR `logtype`='3') ORDER BY `data` DESC, `data_dodania` DESC LIMIT 2");
    $row = mysqli_fetch_row($result);
    list($lat0, $lon0) = $row;
    //print_r($row);
    $row = mysqli_fetch_row($result);
    list($lat1, $lon1) = $row;
    //print_r($row);

    mysqli_free_result($result);
    if (!empty($lat0) and !empty($lon0) and !empty($lat1) and !empty($lon1)) {
        $lat1 = deg2rad($lat1);
        $lat0 = deg2rad($lat0);
        $lon1 = deg2rad($lon1);
        $lon0 = deg2rad($lon0);

        $droga = round(1.852 * 60 * rad2deg(2 * asin(sqrt(pow((sin(($lat0 - $lat1) / 2)), 2) + cos($lat0) * cos($lat1) * pow((sin(($lon1 - $lon0) / 2)), 2)))));
    } else {
        return 0;
    }

    return $droga;
}
