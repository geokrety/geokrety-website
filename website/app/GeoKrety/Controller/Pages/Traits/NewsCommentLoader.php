<?php

use GeoKrety\Model\NewsComment;
use GeoKrety\Service\Smarty;

trait NewsCommentLoader {
    /**
     * @var NewsComment
     */
    protected $comment;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        $comment = new NewsComment();
        $this->comment = $comment;
        $comment->load(['id = ?', $f3->get('PARAMS.newscommentid')]);
        if ($comment->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            exit();
        }
        if (!$comment->isAuthor()) {
            http_response_code(403);
            Smarty::render('dialog/alert_403.tpl');
            exit();
        }

        Smarty::assign('comment', $comment);
    }
}
