<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\Picture;
use GeoKrety\Model\User;

class UsersPictures {
    public function countUserPicturesAll() {
        $userModel = new User();

        $movesUsers = $userModel->count();
        if (!$movesUsers) {
            echo "\e[0;32mNo Users found\e[0m".PHP_EOL;

            return;
        }
        echo sprintf('%d Users to proceed', $movesUsers).PHP_EOL;

        // Paginate the table resultset as it may blow ram!
        define('PER_PAGE', 10);
        $total_pages = ceil($movesUsers / PER_PAGE);
        $counter = 0;
        $counterTotalPictures = 0;
        $counterFixed = 0;

        $picture = new Picture();
        for ($i = 0; $i < $total_pages; ++$i) {
            $subset = $userModel->paginate($i, PER_PAGE);
            foreach ($subset['subset'] as $user) {
                list($totalPictures, $fixed) = $this->processUserPicturesCount($user, $picture);
                $counterTotalPictures += $totalPictures;
                $counterFixed += $fixed;
                ++$counter;
            }
        }

        echo sprintf("\e[0;32mRecomputed %d Users. For a total of %d pictures. %d Fixed\e[0m", $counter, $counterTotalPictures, $counterFixed).PHP_EOL;
    }

    public function generateByUserId(Base $f3) {
        $move = new User();
        $move->load(['id = ?', $f3->get('PARAMS.userid')]);
        if ($move->dry()) {
            echo "\e[0;32mNo such User found\e[0m".PHP_EOL;

            return;
        }
        $this->processUserPicturesCount($move, new Picture());
    }

    private function processUserPicturesCount(User $user, Picture $picture) {
        $picturesCount = $picture->count(['user = ? AND uploaded_on_datetime != ?', $user->id, null]);
        $picturesCountOld = $user->pictures_count;
        $user->pictures_count = $picturesCount;
        $user->save();
        $color = ($picturesCountOld === $picturesCount ? 32 : 31);
        echo sprintf("\e[0;%06dm * User %s has now %d pictures ; %d before\e[0m", $color, $user->id, $picturesCount, $picturesCountOld).PHP_EOL;
        ob_flush();

        return [$picturesCount, ($picturesCountOld === $picturesCount ? 0 : 1)];
    }
}
