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
            $f3->error(404, _('This comment does not exists.'));
        }
        if (!$comment->isAuthor()) {
            $f3->error(403, _('This action is reserved to the author.'));
        }

        Smarty::assign('comment', $comment);
    }
}
