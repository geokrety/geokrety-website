<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Model\Geokret;
use GeoKrety\Service\BaseXClient;

class BaseX {
    use Script;

    /**
     * @throws \Exception
     */
    public function initDB(): void {
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
        $this->console_writer->setPattern('Publishing %7s update status [%6d/%6d] - %3.2f%%');
        $this->script_start(__METHOD__);

        $sql_max_gk = <<<'SQL'
SELECT MAX(gkid) as max_gkid
FROM geokrety.gk_geokrety;
SQL;

        $result = \Base::instance()->get('DB')->exec($sql_max_gk);
        $max_gkid = $result[0]['max_gkid'] ?? 1;

        $sql = <<<'SQL'
SELECT amqp.publish(1, 'geokrety', '', json_build_object(
		'id', ?::text,
		'op', 'UPDATE',
		'kind', 'gk_geokrety'
	)::text);
SQL;
        for ($i = $max_gkid; $i > 0; --$i) {
            $j = $max_gkid - $i + 1;
            $this->console_writer->print([Geokret::id2gkid($i), $j, $max_gkid, $j / $max_gkid * 100]);
            $f3->get('DB')->exec($sql, [$i]);
        }
        $this->script_end();
    }

    public function exportBasic() {
        $basex = BaseXClient::instance()->getSession();
        $export_path = GK_BASEX_EXPORTS_PATH;

        $query = <<<XQUERY
xquery db:export("geokrety", "$export_path", map { "method": "xml", "cdata-section-elements": "description name owner user waypoint application comment message"})
XQUERY;
        $basex->execute($query);
    }

    public function exportDetails() {
        $basex = BaseXClient::instance()->getSession();
        $export_path = GK_BASEX_EXPORTS_PATH;

        $query = <<<XQUERY
xquery db:export("geokrety-details", "$export_path", map { "method": "xml", "cdata-section-elements": "description name owner user waypoint application comment message"})
XQUERY;
        $basex->execute($query);
    }

    public function exportAll() {
        $this->exportBasic();
        $this->exportDetails();
    }
}
