<?php

namespace GeoKrety\Controller;

use GeoKrety\Service\Smarty;
use GeoKrety\Model\NewsComment;

class NewsCommentDelete extends Base {
    public function get(\Base $f3) {
        $comment = $this->load($f3);
        Smarty::assign('comment', $comment);
        Smarty::render('dialog/news_comment_delete.tpl');
    }

    public function post(\Base $f3) {
        $comment = $this->load($f3);
        $newsid = $comment->news->id;
        if ($comment->valid()) {
            $comment->erase();
        }
        $f3->reroute("@news_details(@newsid=$newsid)");
    }

    private function load(\Base $f3) {
        $comment = new NewsComment();
        $comment->load(array('id = ?', $f3->get('PARAMS.newscommentid')));
        if ($comment->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        if (!$comment->isAuthor()) {
            Smarty::render('dialog/alert_403.tpl');
            die();
        }
        return $comment;
    }
}
