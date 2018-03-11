<?php

// chceck antispam token status in mysql database śćńółżś

function chcek_antispam_token($kod)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `kod` FROM `gk-aktywnekody` WHERE `kod`='$kod' LIMIT 1");
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);

    if (empty($row)) {
        // antispam token doesnt exist in kody database
        mysqli_close($link);

        return 0;
    } else {
        // ---- delete used token ----- //
        mysqli_query($link, "DELETE FROM `gk-aktywnekody` WHERE `kod`='$kod' LIMIT 1");
        mysqli_close($link);

        return 1;
    }
}
