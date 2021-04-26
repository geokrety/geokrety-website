<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\User;

class UserBanner extends BaseCleaner {
    protected string $class_name = __CLASS__;

    protected function getModel(): \GeoKrety\Model\Base {
        return new User();
    }

    protected function getModelName(): string {
        return 'Users';
    }

    protected function getParamId(Base $f3): int {
        return $f3->get('PARAMS.userid');
    }

    protected function filterHook(): array {
        return [];
    }

    protected function orderHook(): array {
        return ['order' => 'joined_on_datetime ASC'];
    }

    protected function process($object): void {
        \GeoKrety\Service\UserBanner::generate($object);
        $this->processResult(true);
        $this->print();
    }

    protected function print(): void {
        $this->console_writer->print([$this->percentProcessed, $this->counter, $this->total]);
    }

    protected function getConsoleWriterPattern(): string {
        return 'Generating users banners: %6.2f%% (%d/%d)';
    }
}
