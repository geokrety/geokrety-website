<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use NewsCommentLoader;

class NewsCommentDelete extends Base {
    use NewsCommentLoader;

    public function get(\Base $f3) {
        Smarty::render('dialog/news_comment_delete.tpl');
    }

    public function post(\Base $f3) {
        $comment = $this->comment;
        $newsId = $comment->news->id;
        if ($comment->valid()) {
            $comment->erase();
            \Sugar\Event::instance()->emit('news-comment.deleted', $comment);
        }
        $f3->reroute(sprintf('@news_details(@newsid=%d)', $newsId));
    }
}
