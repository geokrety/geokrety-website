<?php

namespace GeoKrety\Controller\Devel;

use DateTime;
use GeoKrety\GeokretyType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Model\News;
use GeoKrety\Model\OwnerCode;
use GeoKrety\Model\Picture;
use GeoKrety\Model\User;
use GeoKrety\Model\WaypointGC;
use GeoKrety\Model\WaypointOC;
use GeoKrety\PictureType;

/**
 * Class DatabaseSeed.
 */
class DatabaseSeed extends Base {
    public function users_no_terms_of_use(\Base $f3) {
        $this->users($f3, $terms_of_use = false);
    }

    public function users(\Base $f3, $terms_of_use = true) {
        header('Content-Type: text');
        $start_i = $f3->get('GET.i') ?? 1;
        for ($i = $start_i; $i < $f3->get('PARAMS.count') + $start_i; ++$i) {
            $user = new User();
            $user->username = sprintf('username%d', $i);
            $user->password = 'password';
            if (!$f3->exists('GET.noemail') || !filter_var($f3->get('GET.noemail'), FILTER_VALIDATE_BOOLEAN)) {
                $user->_email = sprintf('username%d+qa@geokrety.org', $i);
            }
            if ($f3->exists('GET.email_invalid')) {
                $user->email_invalid = $f3->get('GET.email_invalid');
            }
            $user->preferred_language = 'en';
            $user->account_valid = $f3->get('PARAMS.status') ?? User::USER_ACCOUNT_VALID;
            $user->_secid = sprintf('%s%d', str_repeat('x', 128 - strlen($i)), $i);
            if ($terms_of_use) {
                $user->touch('terms_of_use_datetime');
            }
            if ($user->validate()) {
                $user->save();
                echo sprintf("Create user: %s\n", $user->username);
            } else {
                echo sprintf("Error creating user: %s\n", $user->username);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo "==========\n";
        echo 'done!';
    }

    public function geokrety(\Base $f3) {
        header('Content-Type: text');
        for ($i = 1; $i <= $f3->get('PARAMS.count'); ++$i) {
            $geokret = new Geokret();
            $geokret->name = 'geokrety%02d';
            $geokret->type = GeokretyType::GEOKRETY_TYPE_TRADITIONAL;
            $geokret->created_on_datetime = '2020-08-22 15:30:42';
            if ($f3->exists('PARAMS.userid')) {
                $geokret->owner = $f3->get('PARAMS.userid');
            }
            if ($geokret->validate()) {
                $geokret->save();
                // Change info after initial saving
                $geokret->name = sprintf('geokrety%02d', $geokret->id);
                $geokret->tracking_code = sprintf('TC%04X', $geokret->id);
                $geokret->save();
                echo sprintf("Created GeoKret: %s %s\n", $geokret->gkid, $geokret->tracking_code);
            } else {
                echo sprintf("Error creating GeoKret: %s\n", $geokret->name);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo 'OK';
    }

    public function geokrety_tracking_code_starting_with_gk(\Base $f3) {
        header('Content-Type: text');
        $geokret = new Geokret();
        $geokret->name = 'geokrety starting with GK';
        $geokret->type = GeokretyType::GEOKRETY_TYPE_TRADITIONAL;
        $geokret->created_on_datetime = '2021-11-27 09:31:17';
        if ($f3->exists('PARAMS.userid')) {
            $geokret->owner = $f3->get('PARAMS.userid');
        }
        $geokret->tracking_code = 'GK1234'; // We want to check some legacy TC starting with GK
        if ($geokret->validate()) {
            // Database will not allow us to do so, so hacking something
            $db = $f3->get('DB');
            $db->exec('ALTER TABLE gk_geokrety DISABLE TRIGGER before_20_manage_tracking_code');
            $geokret->save();
            $db->exec('ALTER TABLE gk_geokrety ENABLE TRIGGER before_20_manage_tracking_code');
            echo sprintf("Created GeoKret: %s %s\n", $geokret->gkid, $geokret->tracking_code);
        } else {
            echo sprintf("Error creating GeoKret: %s\n", $geokret->name);
            foreach (\Flash::instance()->getMessages() as $msg) {
                echo sprintf("Reason: %s\n\n", $msg['text']);
            }
        }

        echo 'OK';
    }

    public function geokrety_owner_code(\Base $f3) {
        header('Content-Type: text');
        $geokret = new Geokret();
        $geokret->load(['id = ?', $f3->get('PARAMS.geokretid')]);
        if ($geokret->dry()) {
            echo sprintf("Error loading GeoKret: %d\n", $f3->get('PARAMS.geokretid'));
            exit();
        }

        $ownercode = new OwnerCode();
        $ownercode->geokret = $geokret->id;
        $ownercode->token = $f3->get('PARAMS.ownercode');
        if (!$ownercode->validate()) {
            echo sprintf("Error creating new Owner Code \"%s\" for GeoKret: %d\n", $ownercode->token, $ownercode->geokret->id);
            exit();
        }
        $ownercode->save();
    }

    public function waypointOC(\Base $f3) {
        header('Content-Type: text');
        for ($i = 1; $i <= $f3->get('PARAMS.count'); ++$i) {
            $wpt = new WaypointOC();
            $wpt->waypoint = 'OC%04d';
            $wpt->lat = 43.69365;
            $wpt->lon = 6.86097 + $i - 1;
            if ($wpt->validate()) {
                $wpt->save();
                $wpt->waypoint = sprintf('OC%04d', $wpt->id);
                $wpt->name = sprintf('OC Waypoint %04d', $wpt->id);
                $wpt->save();
                echo sprintf("Create OC wpt id:%s name:%s\n", $wpt->waypoint, $wpt->name);
            } else {
                echo sprintf("Error creating OC wpt: %s\n", $wpt->name);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo 'OK';
    }

    public function waypointGC(\Base $f3) {
        header('Content-Type: text');
        for ($i = 1; $i <= $f3->get('PARAMS.count'); ++$i) {
            $wpt = new WaypointGC();
            $wpt->waypoint = 'GC%04d';
            $wpt->lat = 43.00000;
            $wpt->lon = 7.00000 + ($i - 1) / 100;
            if ($wpt->validate()) {
                $wpt->save();
                $wpt->waypoint = sprintf('GC%04d', $wpt->id);
                $wpt->save();
                echo sprintf("Create GC wpt id:%s name:%s\n", $wpt->waypoint, $wpt->name);
            } else {
                echo sprintf("Error creating GC wpt: %s\n", $wpt->name);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo 'OK';
    }

    public function move(\Base $f3) {
        header('Content-Type: text');
        for ($i = 1; $i <= $f3->get('PARAMS.count'); ++$i) {
            if ($i > 1) {
                sleep(1);
            }

            $geokret = new Geokret();
            $geokret->load(['id = ?', $f3->get('PARAMS.gkid')]);
            $move = new Move();
            $move->geokret = $geokret;
            $move->move_type = $f3->get('PARAMS.move_type');
            if (!is_null($move->geokret->last_log)) {
                $move->moved_on_datetime = $move->geokret->last_log->moved_on_datetime
                    ->add(new \DateInterval('PT1S'))
                    ->format(GK_DB_DATETIME_FORMAT);
            } else {
                $move->moved_on_datetime = $move->geokret->created_on_datetime
                    ->format(GK_DB_DATETIME_FORMAT);
            }
            $move->username = 'someone';
            if ($move->move_type->isCoordinatesRequired()) {
                $move->waypoint = sprintf('GC%04d', $i);
                $move->lat = 43.00000 + ($i - 1) / 100;
                $move->lon = 7.00000 + ($i - 1) / 100;
            }
            if ($move->validate()) {
                $move->save();
                echo sprintf("Create move id:%d geokret:%d waypoint:%s movetype:%d wpt:%s lat:%s lon:%s\n",
                    $move->id, $move->geokret->id, $move->waypoint, $move->move_type->getLogTypeId(),
                    $move->waypoint, $move->lat, $move->lon);
            } else {
                echo sprintf("Error creating move: %d\n", $move->geokret->id);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo 'OK';
    }

    public function move_post(\Base $f3) {
        header('Content-Type: text');
        $geokret = new Geokret();
        $geokret->load(['id = ?', $f3->get('POST.gkid')]);
        $move = new Move();
        $move->geokret = $geokret;
        $move->move_type = $f3->get('POST.move_type');
        if ($f3->exists('POST.moved_on_datetime')) {
            $move->moved_on_datetime = $f3->get('POST.moved_on_datetime');
        } else {
            if (!is_null($move->geokret->last_log)) {
                $move->moved_on_datetime = $move->geokret->last_log->moved_on_datetime
                    ->add(new \DateInterval('PT1S'))
                    ->format(GK_DB_DATETIME_FORMAT);
            } else {
                $move->moved_on_datetime = $move->geokret->created_on_datetime
                    ->format(GK_DB_DATETIME_FORMAT);
            }
        }
        if ($f3->exists('POST.author')) {
            $move->author = $f3->get('POST.author');
        }
        if ($f3->exists('POST.username')) {
            $move->username = $f3->get('POST.username');
        }
        if ($move->move_type->isCoordinatesRequired()) {
            if ($f3->exists('POST.waypoint') && !empty($f3->get('POST.waypoint'))) {
                $move->waypoint = $f3->get('POST.waypoint');
            }
            if ($f3->exists('POST.lat') && !empty($f3->get('POST.lat'))) {
                $move->lat = $f3->get('POST.lat');
            }
            if ($f3->exists('POST.lon') && !empty($f3->get('POST.lon'))) {
                $move->lon = $f3->get('POST.lon');
            }
        }
        if ($f3->exists('POST.comment')) {
            $move->comment = $f3->get('POST.comment');
        }
        if ($f3->exists('POST.app')) {
            $move->app = $f3->get('POST.app');
        }
        if ($f3->exists('POST.app_ver')) {
            $move->app_ver = $f3->get('POST.app_ver');
        }
        if ($move->validate()) {
            $move->save();
            echo sprintf("Create move id:%d geokret:%d waypoint:%s movetype:%d wpt:%s lat:%s lon:%s\n",
                $move->id, $move->geokret->id, $move->waypoint, $move->move_type->getLogTypeId(),
                $move->waypoint, $move->lat, $move->lon);
        } else {
            error_log(sprintf('Error creating move: %d', $move->geokret->id));
            foreach (\Flash::instance()->getMessages() as $msg) {
                error_log(sprintf('Reason: %s', $msg['text']));
            }
            $f3->error(400);
        }

        echo 'OK';
    }

    public function news(\Base $f3) {
        header('Content-Type: text');
        $start_i = $f3->get('GET.i') ?? 1;
        for ($i = 1; $i <= $f3->get('PARAMS.count'); ++$i) {
            $news = new News();
            $news->author = 1;
            $news->content = "News $i content";
            $news->title = "News $i title";
            if ($f3->exists('GET.publish_date')) {
                $news->created_on_datetime = DateTime::createFromFormat('Y-m-d\TH:i:sT', $f3->get('GET.publish_date'))->format(GK_DB_DATETIME_FORMAT);
            } else {
                $news->touch('created_on_datetime');
            }
            if ($news->validate()) {
                $news->save();
                echo sprintf("Create news: %s\n", $news->title);
            } else {
                echo sprintf("Error creating news: %s\n", $news->title);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo "==========\n";
        echo 'done!';
    }

    public function picture(\Base $f3) {
        header('Content-Type: text');
        $start_i = $f3->get('GET.i') ?? 1;
        for ($i = 1; $i <= $f3->get('PARAMS.count'); ++$i) {
            $picture = new Picture();
            $picture->author = $f3->get('PARAMS.userid');
            $picture->bucket = 'fake-bucket';
            $picture->key = 'fake-key';
            $picture->user = $f3->get('PARAMS.userid');
            $picture->filename = 'fake-filename';
            $picture->type = PictureType::PICTURE_USER_AVATAR;
            if ($picture->validate()) {
                $picture->save();
                echo sprintf("Create picture: %s\n", $picture->title);
            } else {
                echo sprintf("Error creating picture: %s\n", $picture->title);
                foreach (\Flash::instance()->getMessages() as $msg) {
                    echo sprintf("Reason: %s\n\n", $msg['text']);
                }
            }
        }

        echo "==========\n";
        echo 'done!';
    }
}
