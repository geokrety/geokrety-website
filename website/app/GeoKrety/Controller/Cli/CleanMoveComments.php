<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\MoveComment;
use GeoKrety\Service\HTMLPurifier;

class CleanMoveComments extends BaseCleaner {

    protected function getModel(): \GeoKrety\Model\Base {
        return new MoveComment();
    }

    protected function getModelName(): string {
        return 'MoveComment';
    }

    protected function getParamId(Base $f3): int {
        return $f3->get('PARAMS.commentid');
    }

    protected function filterHook() {
        return $this->_filterByUpdatedOnDatetime();
    }

    protected function process(\GeoKrety\Model\Base &$object): void {
        $fixed = false;

        $origText = $object->content;
        $object->content = $this->purifier->purify($origText);
        $fixed = $fixed || ($origText !== $object->content);
        if ($fixed) {
            $object->update();
        }
        $this->processResult($object->id, $fixed);
    }

}
