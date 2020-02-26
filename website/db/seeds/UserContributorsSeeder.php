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
                'daily_mails_hour' => 0,
                'terms_of_use_datetime' => date('Y-m-d H:i:s'),
                'secid' => uniqid(),
            ];
        }

        $this->table('gk-users')->insert($data)->save();
    }
}
