<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\GeokretWithDetails;
use GeoKrety\Model\Move;

abstract class BaseExport extends BaseXML {
    /**
     * @var array The filter to apply
     */
    private array $filter = [[], []];

    protected function loadGeokrety() {
        $geokret = new GeokretWithDetails();
        $res = $geokret->find($this->getFilter());
        foreach ($res ?: [] as $object) {
            $this->processAddGeokret($object);
        }
        $this->processPostHook();
    }

    protected function loadGeokretyPaginated() {
        // Find GeoKrety
        $geokret = new GeokretWithDetails();
        $subset_position = 0;
        do {
            $subset = $geokret->paginate($subset_position, 1000, $this->getFilter(), null, 0);
            $subset_total = $subset['count'];
            $subset_position = ++$subset['pos'];
            foreach ($subset['subset'] ?: [] as $object) {
                $this->processAddGeokret($object);
            }
            $this->processPostHook();
        } while ($subset_position < $subset_total);
    }

    abstract protected function processAddGeokret(&$geokret);

    abstract protected function processAddMove(&$move);

    abstract protected function processPostHook();

    protected function getFilter() {
        if (sizeof($this->filter[0]) === 0) {
            return null;
        }

        return [
            implode('AND ', $this->filter[0]),
            ...$this->filter[1],
        ];
    }

    /**
     * @param array|null $params
     */
    protected function setFilter(string $query, ...$params) {
        $this->filter[0][] = $query;
        if (!is_null($params)) {
            array_push($this->filter[1], ...$params);
        }
    }

    protected function hasFilter(): bool {
        return sizeof($this->filter[0]) === 0;
    }

    protected function loadMoves() {
        // Find Moves
        $move = new Move();
        $subset_position = 0;
        do {
            $subset = $move->paginate($subset_position, 1000, $this->getFilter());
            $subset_total = $subset['count'];
            $subset_position = ++$subset['pos'];
            foreach ($subset['subset'] ?: [] as $object) {
                $this->processAddMove($object);
            }
            $this->processPostHook();
        } while ($subset_position < $subset_total);
    }
}
