<?php

use Phinx\Seed\AbstractSeed;

class UserContributorsSeeder extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run() {
        $data = [];

        foreach (GeoKrety\Controller\HallOfFame::CONTRIBUTORS_IDS as $username) {
            $data[] = [
                'username' => $username,
                'password' => \GeoKrety\Auth::hash_password($username),
                'registration_ip' => '127.0.0.1',
            ];
        }

        $this->table('gk_users')->insert($data)->save();
    }
}
