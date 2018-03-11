<?php

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}
