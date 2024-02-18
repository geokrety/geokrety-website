<?php

namespace GeoKrety\Controller;

use GeoKrety\Model\Geokret;
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
        $this->processPreHook();
        foreach ($res ?: [] as $object) {
            $this->processPreHookAddGeokret($object);
            $this->processAddGeokret($object);
            $this->processPostHookAddGeokret($object);
        }
        $this->processPostHook();
    }

    protected function loadGeokretyPaginated() {
        // Find GeoKrety
        $geokret = new GeokretWithDetails();
        $geokret->filter('moves', null, ['order' => 'moved_on_datetime DESC', 'limit' => GK_API_EXPORT_GEOKRET_DETAILS_MOVES_LIMIT]);
        $options = ['order' => 'id ASC'];
        $subset_position = 0;
        $this->processPreHook();
        do {
            $subset = $geokret->paginate($subset_position, 1000, $this->getFilter(), $options, 0);
            $subset_total = $subset['count'];
            $subset_position = ++$subset['pos'];
            $i = 0;
            foreach ($subset['subset'] ?: [] as $object) {
                $this->processPreHookAddGeokret($object, ++$i, $subset_total, $subset_position, 1000);
                $this->processAddGeokret($object);
                $this->processPostHookAddGeokret($object, $i, $subset_total, $subset_position, 1000);
            }
            $this->processPostHook();
        } while ($subset_position < $subset_total);
    }

    abstract protected function processAddGeokret(&$geokret);

    abstract protected function processAddMove(&$move);

    abstract protected function processPostHook();

    protected function processPreHook() {
        if ($this->f3->exists('GET.secid')) {
            Login::disconnectUser($this->f3);
        }
    }

    protected function getFilter() {
        if (sizeof($this->filter[0]) === 0) {
            return null;
        }

        return [
            implode(' AND ', $this->filter[0]),
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
        $options = ['order' => 'id ASC'];
        $subset_position = 0;
        $this->processPreHook();
        do {
            $subset = $move->paginate($subset_position, 1000, $this->getFilter(), $options, 0);
            $subset_total = $subset['count'];
            $subset_position = ++$subset['pos'];
            $i = 0;
            foreach ($subset['subset'] ?: [] as $object) {
                $this->processPreHookAddMove($object, ++$i, $subset_total, $subset_position, 1000);
                $this->processAddMove($object);
                $this->processPostHookAddMove($object, $i, $subset_total, $subset_position, 1000);
            }
            $this->processPostHook();
        } while ($subset_position < $subset_total);
    }

    protected function processPreHookAddGeokret(Geokret &$geokret, ?int $current = null, ?int $count = null, ?int $subset_position = null, ?int $subset_total = null) {
    }

    protected function processPostHookAddGeokret(Geokret &$geokret, ?int $current = null, ?int $count = null, ?int $subset_position = null, ?int $subset_total = null) {
    }

    protected function processPreHookAddMove(Move &$move, ?int $current = null, ?int $count = null, ?int $subset_position = null, ?int $subset_total = null) {
    }

    protected function processPostHookAddMove(Move &$move, ?int $current = null, ?int $count = null, ?int $subset_position = null, ?int $subset_total = null) {
    }
}
