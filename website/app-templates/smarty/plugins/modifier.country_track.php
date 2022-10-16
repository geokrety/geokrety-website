<?php

use GeoKrety\Model\Geokret;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.country_track.php
 * Type:     modifier
 * Name:     picture
 * Purpose:  outputs a GeoKret country track
 * -------------------------------------------------------------.
 *
 * @throws \SmartyException
 */
function smarty_modifier_country_track(?Geokret $geokret): string {
    if (is_null($geokret)) {
        return '';
    }

    $template_string = <<<'EOT'
{foreach $country_track as $country name=loop1}
    {$country.country|country nofilter}
    <small>({$country.move_count}){if not $smarty.foreach.loop1.last} 🠖 {/if}</small>
{/foreach}
EOT;

    $smarty = GeoKrety\Service\Smarty::getSmarty();
    $smarty->assign('country_track', $geokret->countryTrack());
    $html = $smarty->fetch('string:'.$template_string);

    return $html;
}
