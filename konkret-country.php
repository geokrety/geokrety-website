<?php

require_once '__sentry.php';

// cykl flag

$result = mysqli_query($link,
    "SELECT `country` FROM `gk-ruchy`
WHERE `id` = '$id' AND logtype != '4'
ORDER BY `data` ASC, `data_dodania`"
);

$count = 1;
$last = '';

while ($row = mysqli_fetch_array($result)) {
    list($country) = $row;
    if ($country != '') {
        $countrycap = strtoupper($country);
        if ($last == $country) {
            ++$count;
        } else {
            if ($last != '') {
                $cykl_flag .= "<span class='xxs'>($count)</span>";
                $cykl_flag .= "<img src='".CONFIG_CDN_ICONS."/arrow_flag.png' class='textalign' alt=' -&gt; '/>";
            }
            $count = 1;
            $last = $country;
            $cykl_flag .= "<img src='".CONFIG_CDN_COUNTRY_FLAGS."/$country.png' class='textalign' alt='$countrycap' title='$countrycap' width='16' height='11' />";
        }
    }
}
if (isset($cykl_flag)) {
    $cykl_flag .= "<span class='xxs'>($count)</span>";
}

unset($country);

mysqli_free_result($result);
