<?php

use GeoKrety\Model\MoveComment;
use GeoKrety\Service\Smarty;

trait MoveCommentLoader {
    protected MoveComment $comment;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        if (!is_numeric($f3->get('PARAMS.movecommentid'))) {
            $f3->error(404, _('This comment does not exist.'));
        }

        $comment = new MoveComment();
        $comment->load(['id = ?', $f3->get('PARAMS.movecommentid')]);
        if ($comment->dry()) {
            $f3->error(404, _('This comment does not exist.'));
        }
        if (!$comment->isAuthor()) {
            $f3->set('ERROR_REDIRECT', $this->comment->move->reroute_url);
            $f3->error(403, _('This action is reserved to the author.'));
        }
        $this->comment = $comment;
        Smarty::assign('comment', $comment);
    }
}
