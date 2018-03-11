<?php

// identify the kret śćńółż

function whoiskret($id)
{
    include 'templates/konfig.php';

    $link = DBConnect();

    // user chcek
    $result2 = mysqli_query($link, "SELECT `nazwa`, `owner`, `data` FROM `gk-geokrety` WHERE `id` = '$id' LIMIT 1");
    $row2 = mysqli_fetch_array($result2);
    mysqli_free_result($result2);

    return $row2;
}
