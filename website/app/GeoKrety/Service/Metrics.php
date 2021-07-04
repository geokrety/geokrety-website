<?php

namespace GeoKrety\Service;

use Base;
use Prefab;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;

class Metrics extends Prefab {
    private CollectorRegistry $collector;

    public function __construct() {
        $this->collector = CollectorRegistry::getDefault();
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public static function counter(string $name, string $description, array $labels = [], array $labels_values = []) {
        self::getCollectorRegistry()
            ->getOrRegisterCounter('geokrety', $name, $description, $labels)
            ->inc($labels_values);
    }

    public static function getCollectorRegistry(): CollectorRegistry {
        return self::instance()->collector;
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public static function gauge_set_sql(string $name, string $help, string $sql, array $labels = []): void {
        $results = Base::instance()->get('DB')->exec($sql);
        $gauge = self::getOrRegisterGauge($name, $help, $labels);
        if (sizeof($labels) === 0) {
            //foreach ($results as $value) {
            //}
            $gauge->set($results[0]['count']);

            return;
        }
        foreach ($results as $value) {
            $gauge->set($value['count'], [$value['label']]);
        }
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public static function getOrRegisterCounter(string $name, string $help, array $labels = []): Counter {
        return self::instance()->collector
            ->getOrRegisterCounter('geokrety', $name, $help, $labels);
    }

    /**
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public static function getOrRegisterGauge(string $name, string $help, array $labels = []): Gauge {
        return self::instance()->collector
            ->getOrRegisterGauge('geokrety', $name, $help, $labels);
    }
}
