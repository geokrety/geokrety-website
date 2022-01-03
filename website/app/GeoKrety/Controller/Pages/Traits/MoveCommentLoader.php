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
            $f3->error(404, _('This comment does not exists.'));
        }
        if (!$comment->isAuthor()) {
            $f3->error(403, _('This action is reserved to the author.'));
        }
        $this->comment = $comment;
        Smarty::assign('comment', $comment);
    }
}
