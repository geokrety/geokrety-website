<?php

namespace Geokrety\Repository;

abstract class AbstractRepository {
    // database session opened with DBConnect();
    protected $dblink;

    // report current activity to stdout
    protected $verbose;

    // common validation service
    protected $validationService;

    public function __construct($dblink, $verbose = false) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
        $this->validationService = new \Geokrety\Service\ValidationService();
    }

    /**
     * Function that return the next index for pagination.
     *
     * @return string
     */
    protected function paginate($count, $curpage, $perpage) {
        $max_page = ceil($count / $perpage);

        if ($curpage > $max_page) {
            $curpage = $max_page;
        }
        if ($curpage < 1) {
            $curpage = 1;
        }

        return ($curpage - 1) * $perpage;
    }

    public function count($where, array $params) {
        $sql = $this->count.' '.$where;

        if ($this->verbose) {
            echo "\n$sql\n";
        }

        if (!($stmt = $this->dblink->prepare($sql))) {
            throw new \Exception('count prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if (!$stmt->bind_param(...$params)) {
            throw new \Exception('count binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception('count execute failed: ('.$stmt->errno.') '.$stmt->error);
        }

        $stmt->store_result();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        return $total;
    }
}
