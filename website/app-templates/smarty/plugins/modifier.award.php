<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.award.php
 * Type:     modifier
 * Name:     award
 * Purpose:  outputs a award image
 * -------------------------------------------------------------.
 *
 * @param \GeoKrety\Model\Awards|\GeoKrety\Model\AwardsWon $award
 *
 * @throws \SmartyException
 */
function smarty_modifier_award($award, bool $ImageOnly = true): string {
    if ($ImageOnly) {
        $template_string = <<<'EOT'
<img src="{$award->url}" title="{$award->description}" />
EOT;
    } else {
        $template_string = <<<'EOT'
<figure>
    <img src="{$award->url}" alt="{$award->filename}" class="img-thumbnail">
    <figcaption>{$award->description}</figcaption>
</figure>
EOT;
    }

    $smarty = clone GeoKrety\Service\Smarty::getSmarty();
    $smarty->assign('award', $award);
    $html = $smarty->fetch('string:'.$template_string);
    $smarty->clearAssign(['award']);

    return $html;
}
