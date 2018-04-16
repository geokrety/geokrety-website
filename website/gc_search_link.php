<?php

// generate link to gc search for a cache at given coords

function gc_search_link($lat, $lon, $dist = 1)
{
    //include("templates/konfig.php");

    $return = "http://www.geocaching.com/seek/nearest.aspx?origin_lat=$lat&origin_long=$lon&dist=$dist";

    return $return;
}
