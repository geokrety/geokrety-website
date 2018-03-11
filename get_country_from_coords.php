<?php

function get_country_from_coords($lat, $lon)
{
    if (is_null($lat) || is_null($lon)) {
        return 'xyz';
    }

    $country = file_get_contents("https://geo.kumy.org/api/getCountry?lat=$lat&lon=$lon");
    if ($country === false || empty($country)) {
        return 'xyz';
    }

    return strtolower($country);
}
