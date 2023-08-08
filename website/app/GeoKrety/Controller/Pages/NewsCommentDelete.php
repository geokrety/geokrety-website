<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;

class NewsCommentDelete extends Base {
    use \NewsCommentLoader;

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/news_comment_delete.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/news_comment_delete.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $comment = $this->comment;
        $newsId = $comment->news->id;
        if ($comment->valid()) {
            $comment->erase();
            \Sugar\Event::instance()->emit('news-comment.deleted', $comment);
        }
        $f3->reroute(sprintf('@news_details(@newsid=%d)', $newsId));
    }
}
