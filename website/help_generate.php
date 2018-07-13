<?php

require_once '__sentry.php';

// generates help .... śćńółźćą

// smarty cache -- above this declaration should be wybierz_jezyk.php!
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$TYTUL = _('Help');

$HEAD .= '<style type="text/css">.logtypes TD{padding:5px; vertical-align: middle; border-bottom: 1px solid #ccc}
.logtypes .mid TD{text-align: middle;}
</style>';

$jezyk = strtolower($_GET['help']); // w jamim języku ma być help?
if (!in_array($jezyk, ['en', 'cz', 'de', 'fr', 'hu', 'pl', 'ru', 'sk'])) {
    die;
}

$TRESC = file_get_contents("help/$jezyk/help.html");

$socialGroups = $config['gk_social_groups'];

$groupsTable = '<table style="padding:15px;" cellpadding="10">'
               .'<thead><tr>'
               .'<th>'.$socialGroups[0]['lang'].'</th>'
               .'<th>'.$socialGroups[0]['service'].'</th>'
               .'<th>'.$socialGroups[0]['title'].'</th>'
               .'</tr></thead>'
               .'<tbody>';
for ($i = 1; $i < count($socialGroups); ++$i) {
    $groupsTable .= '<tr>'
                   .'<td style="padding:4px;"><img src="https://cdn.geokrety.org/images/country-codes/'
                   .$socialGroups[$i]['flag']
                   .'.png"/>'
                   .'&#160;'.$socialGroups[$i]['lang'].'</td>'
                   .'<td>'.$socialGroups[$i]['service'].'</td>'
                   .'<td><a href="'.$socialGroups[$i]['link'].'">'.$socialGroups[$i]['title'].'</td>'
                   .'</tr>';
}
    $groupsTable .= '</tbody></table>';

// replace #GK_SOCIAL_GROUPS# with table of social groups
$TRESC = str_replace('#GK_SOCIAL_GROUPS#', "$groupsTable", $TRESC);

// --------------------------------------------------------------- SMARTY ---------------------------------------- //
// ----------------------------------------------JSON-LD---------------------------
$gkName = 'geokrety.org';
$gkUrl = $config['adres'];
$gkLogoUrl = $config['cdn_url'].'/images/banners/geokrety.png';

$helpHeadline = 'GeoKrety '._('Help').' ('.$jezyk.')';
$helpDescription = 'help page';
$helpUrl = $config['adres'].'help_generate.php?help='.$jezyk;
$helpImage = $config['cdn_url'].'/images/log-icons/0/icon.jpg';
$helpKeywords = 'geokrety,opencaching,help';
$helpLang = $jezyk;
if ($jezyk == 'cz') {
    $helpLang = 'cs'; // LD-JSON requires ISO-639 value
}
$helpLastUpdate = filemtime(__FILE__);
$helpMainEntityOf = $config['adres'];

$ldHelper = new LDHelper($gkName, $gkUrl, $gkLogoUrl);
$ldJSONArticle = $ldHelper->helpArticle(
        $helpHeadline,
        $helpDescription,
        $helpUrl,
        $helpImage,
        $helpKeywords,
        $helpLang,
        date('c', $helpLastUpdate),
        date('c', $helpLastUpdate),
        $helpMainEntityOf
        );

$TRESC .= $ldJSONArticle;
// ----------------------------------------------JSON-LD-(end)---------------------

require_once 'smarty.php';
