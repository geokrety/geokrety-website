<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Move;
use GeoKrety\Model\MoveComment;
use GeoKrety\Service\Smarty;

class MoveCommentCreate extends Base {
    use \MoveLoader;

    public function get_comment(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/move_comment_create.tpl');
    }

    public function get_comment_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/move_comment_create.tpl');
    }

    private function _get_missing(\Base $f3) {
        if (!$this->move->isGeoKretLastPosition()) {
            $f3->set('ERROR_REDIRECT', $this->move->geokret->url);
            $f3->error(400, _('Missing status can only be added to current GeoKret position.'));
        }
    }

    public function get_missing(\Base $f3) {
        $this->_get_missing($f3);
        Smarty::render('extends:full_screen_modal.tpl|dialog/move_comment_create_missing.tpl');
    }

    public function get_missing_ajax(\Base $f3) {
        $this->_get_missing($f3);
        Smarty::render('extends:base_modal.tpl|dialog/move_comment_create_missing.tpl');
    }

    public function post_comment(\Base $f3) {
        $this->checkCsrf('get_comment');
        $gkid = $this->move->geokret->gkid;
        $comment = new MoveComment();
        $comment->move = $this->move;
        $comment->geokret = $this->move->geokret;
        $comment->author = $f3->get('SESSION.CURRENT_USER');
        $comment->content = $f3->get('POST.comment');
        $comment->type = 0;

        if ($comment->validate()) {
            $comment->save();
            \Sugar\Event::instance()->emit('move-comment.created', $comment);
            \Flash::instance()->addMessage(_('Comment saved.'), 'success');
        } else {
            Smarty::assign('comment', $comment);
            $this->get_comment($f3);
            exit();
        }

        $f3->reroute(sprintf('@geokret_details_paginate(@gkid=%s,@page=%d)#log%d', $gkid, $comment->move->getMoveOnPage(), $comment->move->id));
    }

    public function post_missing(\Base $f3) {
        $this->checkCsrf('get_missing');
        $gkid = $this->move->geokret->gkid;
        $comment = new MoveComment();
        $comment->move = $this->move;
        $comment->geokret = $this->move->geokret;
        $comment->author = $f3->get('SESSION.CURRENT_USER');
        $comment->content = $f3->get('POST.comment');
        $comment->type = 1;

        if ($comment->validate()) {
            $comment->save();
            \Sugar\Event::instance()->emit('move-comment.created', $comment);
            \Flash::instance()->addMessage(_('Comment saved.'), 'success');
        } else {
            Smarty::assign('comment', $comment);
            $this->get_missing($f3);
            exit();
        }

        $f3->reroute("@geokret_details(@gkid=$gkid)#log".$comment->move->id);
    }

    protected function checkAuthor(Move $move) {
        // Empty
    }
}
