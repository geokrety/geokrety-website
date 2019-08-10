<?php

namespace GeoKrety\Validation;

class NewsCommentValidator extends Base {
    protected $comment = null;

    public function validate(\GeoKrety\Model\NewsComment $comment): bool {
        $this->comment = $comment;
        $this->checkContentNotNull();

        return !$this->hasErrors;
    }

    protected function checkContentNotNull() {
        if (Base::isEmpty($this->comment->content)) {
            $this->hasErrors = true;
            \Flash::instance()->addMessage(_('Comment could not be empty.'), 'danger');

            return false;
        }

        return true;
    }
}
