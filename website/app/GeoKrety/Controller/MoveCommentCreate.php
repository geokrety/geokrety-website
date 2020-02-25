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

    public function get_missing(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/move_comment_create_missing.tpl');
    }

    public function get_missing_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/move_comment_create_missing.tpl');
    }

    public function post_comment(\Base $f3) {
        $gkid = $this->move->geokret->gkid;
        $comment = new MoveComment();
        $comment->move = $this->move;
        $comment->geokret = $this->move->geokret;
        $comment->author = $f3->get('SESSION.CURRENT_USER');
        $comment->content = $f3->get('POST.comment');

        if ($comment->validate()) {
            $comment->save();
            \Event::instance()->emit('move-comment.created', $comment);
            \Flash::instance()->addMessage(_('Comment saved.'), 'success');
        } else {
            Smarty::assign('comment', $comment);
            $this->get_comment($f3);
            die();
        }

        $f3->reroute("@geokret_details(@gkid=$gkid)#log".$comment->move->id);
    }

    public function post_missing(\Base $f3) {
        $gkid = $this->move->geokret->gkid;
        $comment = new MoveComment();
        $comment->move = $this->move;
        $comment->geokret = $this->move->geokret;
        $comment->author = $f3->get('SESSION.CURRENT_USER');
        $comment->content = $f3->get('POST.comment');
        $comment->type = '1';

        if ($comment->validate()) {
            $comment->save();
            \Event::instance()->emit('move-comment.created', $comment);
            \Flash::instance()->addMessage(_('Comment saved.'), 'success');
        } else {
            Smarty::assign('comment', $comment);
            $this->get_missing($f3);
            die();
        }

        $f3->reroute("@geokret_details(@gkid=$gkid)#log".$comment->move->id);
    }

    protected function checkAuthor(Move $move) {
        // Empty
    }
}
