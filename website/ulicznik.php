<?php


if (!function_exists(licznikejro)) {
    function ulicznik($witryna)
    {
        // licznik mySQL
        // argument:    nazwa dla licznika
        // zwraca:  ile, srednio, od kiedy

        $dzisiaj = time();

        $query = "SELECT  `witryna`, `licznik`,  DATE(`od`) FROM `gk-licznik` WHERE `witryna` = '$witryna' LIMIT 1";

        require 'templates/konfig.php';
        $link = DBConnect();

        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);

        $ile = $row[1];

        session_start();
        //if(empty($_SESSION[$sesid . "geokrety" . $row[0]])){
        //        $_SESSION[$sesid . "geokrety" .  $row[0]] = 1;

        if (empty($_SESSION['geokrety'.$row[0]])) {
            $_SESSION['geokrety'.$row[0]] = 1;

            $ile = $row[1] + 1;
            $query = "UPDATE `gk-licznik` SET `licznik` = '$ile' WHERE `witryna` = '$witryna'";
            $result = mysqli_query($link, $query);
            //mysqli_free_result($link, $result);
        }

        $srednio = sprintf('%.1f', $ile / (($dzisiaj - strtotime($row[2])) / 86400));

        $tablica = array($ile, $srednio, $row[2]);

        return $tablica;
    }
}
