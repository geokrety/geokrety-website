<?php

namespace GeoKrety\Controller;

use DateTime;
use Flash;
use GeoKrety\LogType;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Validation\Coordinates as CoordinatesValidation;
use GeoKrety\Service\Validation\TrackingCode as TrackingCodeValidation;
use GeoKrety\Service\Validation\Waypoint as WaypointValidation;
use ReCaptcha\ReCaptcha;
use Sugar\Event;

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
            exit();
        }

        if (!$this->move->isAuthor()) {
            http_response_code(403);
            Smarty::render('dialog/alert_403.tpl');
            exit();
        }
    }

    public function get(\Base $f3) {
        if ((is_null($this->move->geokret) || is_null($this->move->geokret->tracking_code)) && $f3->exists('GET.tracking_code')) {
            $geokret = new Geokret();
            $geokret->load(['tracking_code = ?', strtoupper($f3->get('GET.tracking_code'))]);
            if (!$geokret->dry()) {
                $this->move->geokret = $geokret;
            }
        }
        if (!LogType::isValid($this->move->move_type->getLogTypeId()) && $f3->exists('GET.move_type')) {
            $this->move->move_type = $f3->get('GET.move_type');
        }
        if (is_null($this->move->waypoint) && $f3->exists('GET.waypoint')) {
            $this->move->waypoint = $f3->get('GET.waypoint');
        }
        if (is_null($this->move->lat) && is_null($this->move->lon) && $f3->exists('GET.coordinates')) {
            $coordChecker = new CoordinatesValidation();
            if ($coordChecker->validate($f3->get('GET.coordinates'))) {
                $this->move->lat = $coordChecker->getLat();
                $this->move->lon = $coordChecker->getLon();
            }
        }
        Smarty::render('pages/geokret_move.tpl');
    }

    public function post(\Base $f3) {
        $errors = [];
        $move = $this->move;
        $isEdit = !is_null($move->id);

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
        $date = DateTime::createFromFormat('Y-m-d H:i:s T', sprintf(
                '%s %s:%s:00 %s',
                $f3->get('POST.date'),
                str_pad($f3->get('POST.hour'), 2, '0', STR_PAD_LEFT),
                str_pad($f3->get('POST.minute'), 2, '0', STR_PAD_LEFT),
                $f3->get('POST.tz') ?? 'UTC'
        ));
        if ($date === false) {
            $errors = array_merge($errors, [_('The date time could not be parsed.')]);
        } else {
            $move->moved_on_datetime = $date->format(GK_DB_DATETIME_FORMAT);
        }

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

        // We use the first move to retrieve other fields (date, author etc)
        // Permit to display again on form error.
        if (sizeof($moves) < 1) {
            $moves[] = clone $move;
        }
        // We use the first move to retrieve other fields (date, author etc)
        Smarty::assign('move', $moves[0]);

        // reCaptcha
        if (!$f3->get('SESSION.CURRENT_USER') && GK_GOOGLE_RECAPTCHA_SECRET_KEY) {
            $recaptcha = new ReCaptcha(GK_GOOGLE_RECAPTCHA_SECRET_KEY);
            $resp = $recaptcha->verify($f3->get('POST.g-recaptcha-response'), $f3->get('IP'));
            if (!$resp->isSuccess()) {
                $errors = array_merge($errors, [_('reCaptcha failed!')]);
            }
        }

        // Check for errors
        $this->renderErrors($errors, $moves);

        // Save the moves
        foreach ($moves as $_move) {
            $_move->save();
            if ($isEdit) {
                Event::instance()->emit('move.updated', $_move);
            } else {
                Event::instance()->emit('move.created', $_move);
            }
        }

        // Do we have some errors while saving to database?
        if ($f3->get('ERROR')) {
            Flash::instance()->addMessage(_('Failed to save move.'), 'danger');
            $this->get($f3);
        } else {
            Flash::instance()->addMessage(_('Your move has been saved.'), 'success');
            $f3->reroute(sprintf('@geokret_details_paginate(@gkid=%s,@page=%d)#log%d', $moves[0]->geokret->gkid, $moves[0]->getMoveOnPage(), $moves[0]->id));
        }
    }

    protected function _checkErrors(array &$errors, $moves) {
        $hasError = sizeof($errors) > 0;
        foreach ($moves as $_move) {
            if (!$_move->validate()) {
                $hasError = true;
            }
        }
        if ($hasError and $this->f3->exists('validation.error')) {
            $errors = array_merge($errors, $this->f3->get('validation.error'));
        }

        return $hasError;
    }

    protected function renderErrors(array $errors, $moves) {
        $hasError = $this->_checkErrors($errors, $moves);
        foreach ($errors as $err) {
            Flash::instance()->addMessage($err, 'danger');
        }
        // Display the form again if some errors are present
        if ($hasError) {
            $this->get($this->f3);
            exit();
        }
    }
}
