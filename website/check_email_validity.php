<?php

function check_email_validity($userid, $alert_msgs)
{
    include_once 'db.php';
    $db = new db();

    $sql = "SELECT `email`, `email_invalid` FROM `gk-users` WHERE `userid`='$userid' LIMIT 1";
    $row = $db->exec_fetch_row($sql, $num_rows);
    list($email, $email_invalid) = $row;

    $message = sprintf(_('Your currently configured email address (%s) seems to be invalid. Please <a href="/edit.php?co=email">update your email address</a> in the preferences.'), $email);
    switch ($email_invalid) {
        case 1:
            $alert_msgs[] = array(
              'level' => 'danger',
              'message' => $message,
            );
            break;
        case 2:
            $alert_msgs[] = array(
              'level' => 'warning',
              'message' => $message,
            );
            break;
    }

    return $alert_msgs;
}
