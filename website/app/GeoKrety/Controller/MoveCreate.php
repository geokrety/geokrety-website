<?php

namespace GeoKrety\Controller;

use DateTime;
use Event;
use Flash;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Validation\Coordinates as CoordinatesValidation;
use GeoKrety\Service\Validation\TrackingCode as TrackingCodeValidation;
use GeoKrety\Service\Validation\Waypoint as WaypointValidation;
use ReCaptcha\ReCaptcha;

// use GeoKrety\Service\; // TODO Country / Elevation services

class MoveCreate extends Base {
    /**
     * @var Move
     */
    private $move;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $move = new Move();
        $this->move = $move;
        Smarty::assign('move', $this->move);

        if (!$f3->exists('PARAMS.moveid')) {
            return;
        }

        $this->move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($this->move->dry()) {
            http_response_code(404);
            Smarty::render('dialog/alert_404.tpl');
            die();
        }

        if (!$this->move->isAuthor()) {
            http_response_code(403);
            Smarty::render('dialog/alert_403.tpl');
            die();
        }
    }

    public function get(\Base $f3) {
        $tracking_code = $this->move->geokret->tracking_code ?? ($this->tracking_code ?? $f3->get('GET.tracking_code'));
        Smarty::assign('tracking_code', $tracking_code);
        Smarty::render('pages/geokret_move.tpl');
    }

    public function post(\Base $f3) {
        $errors = [];
        $move = $this->move;
        $isEdit = !is_null($this->move->id);

        $move->move_type = $f3->get('POST.logtype');
        if ($f3->get('SESSION.CURRENT_USER')) {
            $move->author = $f3->get('SESSION.CURRENT_USER');
        } else {
            $move->username = $f3->get('POST.username');
        }
        $move->comment = $f3->get('POST.comment');
        $move->app = $f3->get('POST.app');
        $move->app_ver = $f3->get('POST.app_ver');

        // Datetime parser
        $date = DateTime::createFromFormat('Y-m-d H:i:s T', $f3->get('POST.date').' '.str_pad($f3->get('POST.hour'), 2, '0', STR_PAD_LEFT).':'.str_pad($f3->get('POST.minute'), 2, '0', STR_PAD_LEFT).':00 UTC');
        $move->moved_on_datetime = $date->format(GK_DB_DATETIME_FORMAT);

        if ($move->move_type->isCoordinatesRequired()) {
            // Waypoint validation
            $waypointChecker = new WaypointValidation();
            if ($waypointChecker->validate($f3->get('POST.waypoint'), $f3->get('POST.coordinates'))) {
                $move->waypoint = $waypointChecker->getWaypoint()->waypoint;
                $move->lat = $waypointChecker->getWaypoint()->lat;
                $move->lon = $waypointChecker->getWaypoint()->lon;
            } else {
                $errors = array_merge($errors, $waypointChecker->getErrors());
            }

            // Coordinates validation
            // Allow for coordinates override
            $coordChecker = new CoordinatesValidation();
            if ($coordChecker->validate($f3->get('POST.coordinates'))) {
                if ($move->lat != $coordChecker->getLat() || $move->lon != $coordChecker->getLon()) {
                    $move->lat = $coordChecker->getLat();
                    $move->lon = $coordChecker->getLon();
                }
            } else {
                $errors = array_merge($errors, $coordChecker->getErrors());
            }
        } else {
            // Reset values if no coordinates are required, else the validator will complain
            // Note, in any case, they will be overwritten in Model hook 😆
            $move->waypoint = null;
            $move->lat = null;
            $move->lon = null;
        }

        // Tracking Code parser
        $moves = [];
        $trackingCodeChecker = new TrackingCodeValidation();
        if ($trackingCodeChecker->validate($f3->get('POST.tracking_code'))) {
            foreach ($trackingCodeChecker->getGeokrety() as $geokret) {
                $move_ = clone $move;
                $move_->geokret = $geokret->id;
                $moves[] = $move_;
            }
        } else {
            $errors = array_merge($errors, $trackingCodeChecker->getErrors());
        }
        // Store the input tracking code(s) so it can be disaplyed again on form error
        $this->tracking_code = $f3->get('POST.tracking_code');

        // reCaptcha
        if (!$f3->get('SESSION.CURRENT_USER') && GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($f3->get('POST.g-recaptcha-response'), $f3->get('IP'));
            if (!$resp->isSuccess()) {
                Flash::instance()->addMessage(_('reCaptcha failed!'), 'danger');
                $this->get($f3);
                die();
            }
        }

        // Check for errors
        $error = sizeof($errors) > 0;
        foreach ($errors as $err) {
            Flash::instance()->addMessage($err, 'danger');
        }
        foreach ($moves as $move) {
            if (!$move->validate()) {
                $error = true;
            }
        }
        // Display the form again if some errors are present
        if ($error) {
            $this->get($f3);
            die();
        }

        // Save the moves
        foreach ($moves as $move) {
            $move->save();
            if ($isEdit) {
                Event::instance()->emit('move.updated', $move);
            } else {
                Event::instance()->emit('move.created', $move);
            }
        }
        // Do we have some errors while saving to database?
        if ($f3->get('ERROR')) {
            Flash::instance()->addMessage(_('Failed to save move.'), 'danger');
        } else {
            Flash::instance()->addMessage(_('Your move has been saved.'), 'success');
            $f3->reroute('@geokret_details_paginate(@gkid='.$moves[0]->geokret->gkid.',page='.$moves[0]->getMoveOnPage().')#log'.$moves[0]->id);
        }
        $this->get($f3);
    }
}
