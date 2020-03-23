<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\Move;
use GeoKrety\Model\Picture;

class MovesPictures {
    public function countMovePicturesAll() {
        $moveModel = new Move();

        $movesCount = $moveModel->count();
        if (!$movesCount) {
            echo "\e[0;32mNo Moves found\e[0m".PHP_EOL;

            return;
        }
        echo sprintf('%d Moves to proceed', $movesCount).PHP_EOL;

        // Paginate the table resultset as it may blow ram!
        define('PER_PAGE', 10);
        $total_pages = ceil($movesCount / PER_PAGE);
        $counter = 0;
        $counterTotalPictures = 0;
        $counterFixed = 0;

        $picture = new Picture();
        for ($i = 0; $i < $total_pages; ++$i) {
            $subset = $moveModel->paginate($i, PER_PAGE);
            foreach ($subset['subset'] as $move) {
                list($totalPictures, $fixed) = $this->processMovePicturesCount($move, $picture);
                $counterTotalPictures += $totalPictures;
                $counterFixed += $fixed;
                ++$counter;
            }
        }

        echo sprintf("\e[0;32mRecomputed %d Moves. For a total of %d pictures. %d Fixed\e[0m", $counter, $counterTotalPictures, $counterFixed).PHP_EOL;
    }

    public function generateByMoveId(Base $f3) {
        $move = new Move();
        $move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($move->dry()) {
            echo "\e[0;32mNo such Move found\e[0m".PHP_EOL;

            return;
        }
        $this->processMovePicturesCount($move, new Picture());
    }

    private function processMovePicturesCount(Move $move, Picture $picture) {
        $picturesCount = $picture->count(['move = ? AND uploaded_on_datetime != ?', $move->id, null]);
        $picturesCountOld = $move->pictures_count;
        $move->pictures_count = $picturesCount;
        $move->save();
        $color = ($picturesCountOld === $picturesCount ? 32 : 31);
        echo sprintf("\e[0;%06dm * Move %s has now %d pictures ; %d before\e[0m", $color, $move->id, $picturesCount, $picturesCountOld).PHP_EOL;
        ob_flush();

        return [$picturesCount, ($picturesCountOld === $picturesCount ? 0 : 1)];
    }
}
