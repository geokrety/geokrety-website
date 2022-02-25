<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.newslink.php
 * Type:     modifier
 * Name:     newslink
 * Purpose:  outputs a news link
 * -------------------------------------------------------------.
 */
function smarty_modifier_newslink(GeoKrety\Model\News $news): string {
    return sprintf(
        '<span class="badge">%d</span> <a href="%s%s" ="news-link" data-id="%d">%s</a>',
        $news->comments_count,
        GK_SITE_BASE_SERVER_URL,
        \Base::instance()->alias('news_details', 'newsid='.$news->id),
        $news->id,
        ('Comments')
    );
}
