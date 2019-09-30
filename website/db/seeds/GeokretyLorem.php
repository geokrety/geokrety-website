<?php

use GeoKrety\Model\Geokret;
use Phinx\Seed\AbstractSeed;

class GeokretyLorem extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run() {
        for ($i = 0; $i < 10; ++$i) {
            Geokret::generate();
        }
    }
}
