<?php

use GeoKrety\Model\NewsComment;
use GeoKrety\Service\Smarty;

trait NewsCommentLoader {
    /**
     * @var NewsComment
     */
    protected $comment;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $comment = new NewsComment();
        $comment->load(['id = ?', $f3->get('PARAMS.newscommentid')]);
        if ($comment->dry()) {
            Smarty::render('dialog/alert_404.tpl');
            die();
        }
        if (!$comment->isAuthor()) {
            Smarty::render('dialog/alert_403.tpl');
            die();
        }

        Smarty::assign('comment', $comment);
    }
}
