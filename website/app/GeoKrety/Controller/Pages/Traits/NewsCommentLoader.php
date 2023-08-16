<?php

use GeoKrety\Model\NewsComment;
use GeoKrety\Service\Smarty;

trait NewsCommentLoader {
    protected NewsComment $comment;

    public function beforeRoute(Base $f3) {
        parent::beforeRoute($f3);

        if (!is_numeric($f3->get('PARAMS.newscommentid'))) {
            $f3->error(404, _('This comment does not exist.'));
        }

        $comment = new NewsComment();
        $this->comment = $comment;
        $comment->load(['id = ?', $f3->get('PARAMS.newscommentid')]);
        if ($comment->dry()) {
            $f3->error(404, _('This comment does not exist.'));
        }
        if (!$comment->isAuthor()) {
            $f3->set('ERROR_REDIRECT', $this->comment->news->url);
            $f3->error(403, _('This action is reserved to the author.'));
        }

        Smarty::assign('comment', $comment);
    }
}
