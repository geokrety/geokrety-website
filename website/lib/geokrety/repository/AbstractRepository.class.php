<?php

namespace Geokrety\Repository;

abstract class AbstractRepository {
    // database session opened with DBConnect();
    private $dblink;
    // report current activity to stdout
    private $verbose;

    public function __construct($dblink, $verbose) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
    }
}
