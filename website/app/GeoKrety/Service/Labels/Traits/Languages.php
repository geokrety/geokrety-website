<?php

namespace GeoKrety\Service\Labels\Traits;

trait Languages {
    public array $languages = [];

    public function setLanguages(?array $languages) {
        if (empty($languages)) {
            return;
        }
        $this->languages = $languages;
    }

    /**
     * @return string[]
     */
    public function getLanguages(): array {
        return $this->languages;
    }
}
