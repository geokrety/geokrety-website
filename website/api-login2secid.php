<?php

require_once '__sentry.php';

$kret_haslo1 = $_POST['password'];
$kret_login = $_POST['login'];

if (!empty($kret_login) and !empty($kret_haslo1)) { // logging in with supplied data
    include 'templates/konfig.php';
    include 'czysc.php';
    include_once 'fn_haslo.php';

    function haszuj($str)
    {
        include 'templates/konfig.php';
        $md5_string1 = $config['md5_string1'];
        $md5_string2 = $config['md5_string2'];

        return md5($md5_string1.$str.$md5_string2);
    }

    $login_in = czysc($kret_login);
    $haslo_in = crypt($kret_haslo1, $config['sol']);

    $link = DBConnect();

    $result = mysqli_query($link, "SELECT `user`, `haslo`, `haslo2`, `userid`, `secid` FROM `gk-users` WHERE `user`='$login_in' LIMIT 1");
    $row = mysqli_fetch_row($result);
    list($user, $haslo, $haslo2, $userid, $secid) = $row;
    mysqli_free_result($result);

    if ((empty($row))) {
        echo 'error 1';
        exit;
    } else { // istnieje użytkownik taki //
        // hasla starego typu //
        if ($haslo != '') {
            $haslo_sprawdzone = ($haslo_in == $haslo);
            if ($haslo_sprawdzone) {
                echo 'error 1';
                exit;
            }
        }

        // hasła nowego typu //
        if ($haslo2 != '') {
            $haslo2_sprawdzone = haslo_sprawdz($kret_haslo1, $haslo2);
            if (!$haslo2_sprawdzone) {
                echo 'error 1';
                exit;
            }
        }
    }

    echo "$secid";
} else {
    echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="application/x-www-form-urlencoded" >
<table>
<tr>
<td>Login:</td>
<td><input type="text" name="login" maxlength="30"/></td>
</tr>
<tr>
<td>'._('Password').':</td>
<td><input type="password" name="password" maxlength="20"/></td>
</tr>
</table>
<input type="submit" value=" go " />
</form>
';
}
