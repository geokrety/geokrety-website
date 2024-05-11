<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Model\Base;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\User;

define('PER_PAGE', 1000);

class Sitemap {
    use Script;

    protected int $total = 0;
    protected int $counter = 0;

    protected string $class_name = __CLASS__;
    private \Base $f3;
    private \Multilang $ml;

    public function __construct() {
        $this->initScript();
        $this->console_writer->setPattern('Processing records: %s (%d/%d)');
        $this->f3 = \Base::instance();
    }

    public function processAll(\Base $f3) {
        $this->script_start($this->class_name.'::'.__FUNCTION__);

        $fp = fopen('assets/compressed/sitemap.txt', 'w');

        $aliases = [
            'home',
            'registration',
            'news_list',
            'press_corner',
            'mole_holes',
            'help',
            'contact_us',
            'hall_of_fame',
            'geokrety_map',
        ];
        $this->writeUrl($fp, $this->f3->alias('help_api'));

        $user = new User();
        $geokret = new Geokret();
        $this->ml = \Multilang::instance();
        foreach ($this->ml->languages() as $lang) {
            if ($lang == 'inline-translation') {
                continue;
            }
            echo PHP_EOL."Building: $lang".PHP_EOL;
            foreach ($aliases as $alias) {
                $this->writeUrl($fp, $this->ml->alias($alias, [], $lang));
            }
            $this->processModel($fp, $lang, $user, 'Users', [self::class, 'linkAliasUsers']);
            $this->processModel($fp, $lang, $geokret, 'GeoKrety', [self::class, 'linkAliasGeoKrety']);
        }

        fclose($fp);
        $this->script_end();
    }

    protected function linkAliasGeoKrety(string $lang, Geokret $geokret): string {
        ++$this->counter;

        return $this->ml->alias('geokret_details', '@gkid='.$geokret->gkid, $lang);
    }

    protected function linkAliasUsers(string $lang, User $user): string {
        ++$this->counter;

        return $this->ml->alias('user_details', '@userid='.$user->id, $lang);
    }

    private function processModel($fp, string $lang, Base $model, string $model_name, string|array $link_alias_callback): void {
        $this->counter = 0;
        $this->total = $model->count();
        if (!$this->total) {
            echo $this->console_writer->sprintf("\e[0;32mNo %s found\e[0m", $model_name).PHP_EOL;
            $this->script_end();
        }

        // Paginate the table resultset as it may blow ram!
        $start_page = 0;
        $total_pages = ceil($this->total / PER_PAGE);
        \Base::instance()->get('DB')->log(false);
        $options = ['order' => 'id ASC'];
        for ($i = $start_page; $i < $total_pages; ++$i) {
            $subset = $model->paginate($i, PER_PAGE, null, $options);
            foreach ($subset['subset'] as $object) {
                $url = call_user_func($link_alias_callback, $lang, $object);
                $this->writeUrl($fp, $url);
                $this->console_writer->print([$model_name, $this->counter, $this->total]);
            }
        }
        $this->console_writer->flush();
        echo $this->console_writer->sprintf(PHP_EOL."\e[0;32mAdded %d %s.\e[0m", $this->counter, $model_name).PHP_EOL;
        $this->console_writer->flush();
    }

    private function writeUrl($fp, mixed $url): void {
        fwrite($fp, GK_SITE_BASE_SERVER_URL.$url.PHP_EOL);
    }
}
