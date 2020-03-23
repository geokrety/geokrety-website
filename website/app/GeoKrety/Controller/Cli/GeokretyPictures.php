<?php

namespace GeoKrety\Controller\Cli;

use Base;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Picture;

class GeokretyPictures {
    public function countGeokretPicturesAll() {
        $geokretModel = new Geokret();

        $geokretCount = $geokretModel->count();
        if (!$geokretCount) {
            echo "\e[0;32mNo GeoKrety found\e[0m".PHP_EOL;

            return;
        }
        echo sprintf('%d GeoKrety to proceed', $geokretCount).PHP_EOL;

        // Paginate the table resultset as it may blow ram!
        define('PER_PAGE', 10);
        $total_pages = ceil($geokretCount / PER_PAGE);
        $counter = 0;
        $counterTotalPictures = 0;
        $counterFixed = 0;

        $picture = new Picture();
        for ($i = 0; $i < $total_pages; ++$i) {
            $subset = $geokretModel->paginate($i, PER_PAGE);
            foreach ($subset['subset'] as $geokret) {
                list($totalPictures, $fixed) = $this->processGeokretPicturesCount($geokret, $picture);
                $counterTotalPictures += $totalPictures;
                $counterFixed += $fixed;
                ++$counter;
            }
        }

        echo sprintf("\e[0;32mRecomputed %d GeoKrety. For a total of %d pictures. %d Fixed\e[0m", $counter, $counterTotalPictures, $counterFixed).PHP_EOL;
    }

    public function generateByGeokretId(Base $f3) {
        $geokret = new Geokret();
        $geokret->load(['id = ?', Geokret::gkid2id($f3->get('PARAMS.gkid'))]);
        if ($geokret->dry()) {
            echo "\e[0;32mNo such GeoKret found\e[0m".PHP_EOL;

            return;
        }
        $this->processGeokretPicturesCount($geokret, new Picture());
    }

    private function processGeokretPicturesCount(Geokret $geokret, Picture $picture) {
        $picturesCount = $picture->count(['geokret = ? AND uploaded_on_datetime != ?', $geokret->id, null]);
        $picturesCountOld = $geokret->pictures_count;
        $geokret->pictures_count = $picturesCount;
        $geokret->save();
        $color = ($picturesCountOld === $picturesCount ? 32 : 31);
        echo sprintf("\e[0;%dm * GeoKret %s has now %d pictures ; %d before\e[0m", $color, $geokret->gkid, $picturesCount, $picturesCountOld).PHP_EOL;
        ob_flush();

        return [$picturesCount, ($picturesCountOld === $picturesCount ? 0 : 1)];
    }
}
