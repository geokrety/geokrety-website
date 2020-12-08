<?php

use GeoKrety\Model\MoveComment;
use GeoKrety\Service\Smarty;

trait MoveCommentLoader {
    /**
     * @var MoveComment
     */
    protected $comment;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        $comment = new MoveComment();
        $comment->load(['id = ?', $f3->get('PARAMS.movecommentid')]);
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
        $this->comment = $comment;
        Smarty::assign('comment', $comment);
    }
}
