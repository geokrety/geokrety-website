<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Model\User;

class UserBanner extends BaseCleaner {
    protected function getModel(): \GeoKrety\Model\Base {
        return new User();
    }

    protected function getModelName(): string {
        return 'Users';
    }

    protected function getParamId(\Base $f3): int {
        return $f3->get('PARAMS.userid');
    }

    protected function getScriptName(): string {
        return 'user_banners_generator';
    }

    protected function filterHook() {
        return [];
    }

    protected function orderHook() {
        return ['order' => 'joined_on_datetime ASC'];
    }

    protected function process(&$object): void {
        \GeoKrety\Service\UserBanner::generate($object);
        $this->processResult($object->id, true);
        $this->print();
    }

    protected function print(): void {
        $this->consoleWriter->print([$this->percentProcessed, $this->counter, $this->total]);
    }

    protected function getConsoleWriterPattern() {
        return 'Generating users banners: %6.2f%% (%d/%d)';
    }
}
