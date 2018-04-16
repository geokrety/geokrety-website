<?php

// converts simple arrays to xml (eg errors arrays)

function a2xml($array, $root, $keys)
{
    $now = date('Y-m-d H:i:s');
    $return = '<?xml version="1.0"?><gkxml version="1.0" date="'.$now.'">';
    $return .= "<$root>\n";

    foreach ($array as $rekord) {
        $return .= "<$keys>$rekord</$keys>\n";
    }
    $return .= "</$root></gkxml>";

    return $return;
}
