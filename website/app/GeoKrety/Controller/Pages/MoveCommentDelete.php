<?php

namespace GeoKrety\Controller;

use Flash;
use GeoKrety\Service\Smarty;
use MoveCommentLoader;
use Sugar\Event;

class MoveCommentDelete extends Base {
    use MoveCommentLoader;

    public function get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/move_comment_delete.tpl');
    }

    public function get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/move_comment_delete.tpl');
    }

    public function post(\Base $f3) {
        $this->checkCsrf();
        $comment = $this->comment;
        $gkid = $comment->geokret->gkid;
        $page = $comment->move->getMoveOnPage();
        $move_id = $comment->move->id;

        if ($comment->valid()) {
            $comment->erase();
            Event::instance()->emit('move-comment.deleted', $comment);
            Flash::instance()->addMessage(_('Comment removed.'), 'success');
        } else {
            Flash::instance()->addMessage(_('Failed to delete comment.'), 'danger');
        }
        $f3->reroute(sprintf('@geokret_details_paginate(@gkid=%s,@page=%d)#log%d', $gkid, $page, $move_id));
    }
}
