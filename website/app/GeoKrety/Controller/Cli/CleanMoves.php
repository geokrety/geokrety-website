<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\Move;
use GeoKrety\Service\Markdown;

class CleanMoves extends BaseCleaner {
    protected function getModel(): \GeoKrety\Model\Base {
        return new Move();
    }

    protected function getModelName(): string {
        return 'Move';
    }

    protected function getParamId(Base $f3): int {
        return $f3->get('PARAMS.moveid');
    }

    protected function filterHook() {
        return $this->_filterByUpdatedOnDatetime();
    }

    protected function process(&$object): void {
//        $this->processResult($object->id, true);

        $fixed = false;

        // Clean comment
        $origText = $object->comment;

        // Workaround historical database modifications
        $object->comment = Markdown::toFormattedMarkdown($origText);
        $fixed = $fixed || ($origText !== $object->comment);

        unset($origText);

        if (!is_null($object->username)) {
            $origText = $object->username;
            $object->username = $this->purifier->purify($origText);
            $fixed = $fixed || ($origText !== $object->username);

            unset($origText);
        }

        $object->update();

        $this->processResult($object->id, $fixed);
    }
}
