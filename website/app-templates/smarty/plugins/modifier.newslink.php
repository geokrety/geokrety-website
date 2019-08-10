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
function smarty_modifier_newslink(\GeoKrety\Model\News $news) {
    $badge = '<span class="badge">'.$news->comments_count.'</span>';

    return $badge.' <a href="'.\Base::instance()->alias('news_details', 'newsid='.$news->id).'">'._('Comments').'</a>';
}
