<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.awardlink.php
 * Type:     modifier
 * Name:     awardlink
 * Purpose:  outputs a link to the award ranking
 * -------------------------------------------------------------.
 */
function smarty_modifier_awardlink(?GeoKrety\Model\Awards $award, ?string $alternative_name = null, ?string $target = null): string {
    if (is_null($award) || $award->type !== 'automatic') {
        $username = _('Anonymous');
        if (!is_null($alternative_name)) {
            $username = smarty_modifier_escape($alternative_name);
        }

        return $award->name;
    }
    $target_html = is_null($target) ? '' : ' target="'.$target.'"';

    return sprintf(
        '<a href="%s%s" data-gk-link="award" data-gk-id="%d" title="%s"%s>%s</a>',
        GK_SITE_BASE_SERVER_URL,
        \Base::instance()->alias('statistics_awards_ranking', 'award='.$award->name),
        $award->id,
        sprintf('View "%s" ranking', smarty_modifier_escape($award->name)),
        $target_html,
        smarty_modifier_escape($award->name),
    );
}
