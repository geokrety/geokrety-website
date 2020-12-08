<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.newslink.php
 * Type:     modifier
 * Name:     newslink
 * Purpose:  outputs a news link
 * -------------------------------------------------------------
 */
function smarty_modifier_newslink(GeoKrety\Model\News $news) {
    return sprintf(
        '<span class="badge">%d</span> <a href="%s" ="news-link" data-id="%d">%s</a>',
        $news->comments_count,
        \Base::instance()->alias('news_details', 'newsid='.$news->id),
        $news->id,
        ('Comments')
    );
}
