<?php

// identify the user śćńółż

function whoiswho($id)
{
    include 'templates/konfig.php';

    if ($id == 0) {
        return '(not logged in)';
    } else {
        $link = DBConnect();

        // user chcek
        $result = mysqli_query($link, "SELECT `user` FROM `gk-users` WHERE `userid`='$id' LIMIT 1");
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);

        return $row[0];
    }
}
