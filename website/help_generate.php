<?php

require_once '__sentry.php';

$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';
$TYTUL = _('Help');

// $HEAD .= '<style type="text/css">.logtypes TD{padding:5px; vertical-align: middle; border-bottom: 1px solid #ccc}
// .logtypes .mid TD{text-align: middle;}
// </style>';

$jezyk = strtolower($_GET['help']); // w jamim języku ma być help?
if (!in_array($jezyk, ['en', 'cz', 'de', 'fr', 'hu', 'pl', 'ru', 'sk', 'it'])) {
    die;
}

$TRESC = file_get_contents("help/$jezyk/help.html");

$socialGroups = new \Geokrety\View\SocialGroups($config['gk_social_groups']);

$groupsTable = $socialGroups->toHtmlTable();

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
