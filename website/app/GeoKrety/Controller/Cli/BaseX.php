<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Service\BaseXClient;

class BaseX {
    use Script;

    /**
     * @throws \Exception
     */
    public function initDB(\Base $f3): void {
        $this->script_start(__METHOD__);
        $basex = BaseXClient::instance()->getSession();
        $basex->create('geokrety', '<gkxml><geokrety/></gkxml>');
        $basex->create('geokrety-details', '<gkxml><geokrety/></gkxml>');
        echo $basex->info();
        $this->script_end();
    }

    /**
     * @throws \Exception
     */
    public function importAll(\Base $f3): void {
        $this->script_start(__METHOD__);

        $sql_max_gk = <<<'SQL'
SELECT MAX(gkid) as max_gkid
FROM geokrety.gk_geokrety;
SQL;

        $result = \Base::instance()->get('DB')->exec($sql_max_gk);
        $max_gkid = $result[0]['max_gkid'] ?? 1;

        $sql = <<<'SQL'
INSERT INTO notify_queues.geokrety_changes ("channel", "action", "payload")
VALUES ('gk_geokrety', 'UPDATE', ?);
SQL;
        for ($i = 1; $i <= $max_gkid; ++$i) {
            $f3->get('DB')->exec($sql, [$i]);
        }
        $this->script_end();
    }
}
