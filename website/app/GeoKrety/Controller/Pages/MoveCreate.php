<?php

namespace GeoKrety\Controller;

use Exception;
use Flash;
use GeoKrety\LogType;
use GeoKrety\Model\AuditPost;
use GeoKrety\Model\Geokret;
use GeoKrety\Model\Move;
use GeoKrety\Service\Moves as MovesService;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\Validation\Coordinates as CoordinatesValidation;

class MoveCreate extends Base {
    private Move $move;

    public function beforeRoute($f3) {
        parent::beforeRoute($f3);

        $move = new Move();
        $this->move = $move;
        Smarty::assign('move', $this->move);

        if (!$f3->exists('PARAMS.moveid')) {
            return;
        }

        // From there we are editing a move
        $this->move->load(['id = ?', $f3->get('PARAMS.moveid')]);
        if ($this->move->dry()) {
            $f3->error(404, _('This move does not exist.'));
        }

        if (!$this->move->isAuthor()) {
            $f3->set('ERROR_REDIRECT', $this->move->reroute_url);
            $f3->error(403, _('This action is reserved to the author.'));
        }

        if (!$this->move->move_type->isEditable()) {
            $f3->set('ERROR_REDIRECT', $this->move->reroute_url);
            $f3->error(403, _('This move is not editable.'));
        }
    }

    public function get(\Base $f3) {
        if ($f3->exists('GET.tracking_code') && (is_null($this->move->geokret) || is_null($this->move->geokret->tracking_code))) {
            $geokret = new Geokret();
            $geokret->load(['tracking_code = ?', strtoupper($f3->get('GET.tracking_code'))]);
            if (!$geokret->dry()) {
                $this->move->geokret = $geokret;
            }
        }
        if ($f3->exists('GET.move_type') && !LogType::isValid($this->move->move_type->getLogTypeId())) {
            $this->move->move_type = $f3->get('GET.move_type');
        }
        if (is_null($this->move->waypoint) && $f3->exists('GET.waypoint')) {
            $this->move->waypoint = $f3->get('GET.waypoint');
        }
        if ($f3->exists('GET.coordinates') && is_null($this->move->lat) && is_null($this->move->lon)) {
            $coordChecker = new CoordinatesValidation();
            if ($coordChecker->validate($f3->get('GET.coordinates'))) {
                $this->move->lat = $coordChecker->getLat();
                $this->move->lon = $coordChecker->getLon();
            }
        }
        Smarty::render('pages/geokret_move.tpl');
    }

    public function post(\Base $f3) {
        $move_data = MovesService::postToArray($f3);

        $move_service = new MovesService();
        [$moves, $errors] = $move_service->toMoves($move_data, $this->move);

        // We use the first move to retrieve other fields (date, author etc)
        Smarty::assign('move', sizeof($moves) ? $moves[0] : new Move());

        // Check Csrf
        $csrf_errors = $this->checkCsrf(null);
        $errors = array_merge($errors, !is_null($csrf_errors) ? [$csrf_errors] : []);

        // reCaptcha only for anonymous users
        if (!$f3->get('SESSION.CURRENT_USER')) {
            $captcha_errors = $this->checkCaptcha(null);
            $errors = array_merge($errors, !is_null($captcha_errors) ? [$captcha_errors] : []);
        }

        // Check for errors
        $this->renderErrors($moves, $errors);

        // Save the moves
        try {
            foreach ($moves as $_move) {
                /* @var Move $_move */
                $_move->save();
            }
        } catch (Exception $e) {
            $this->renderErrors($moves, [$e->getMessage()]);
        }

        Flash::instance()->addMessage(_('Your move has been saved.'), 'success');
        $this->f3->reroute($moves[0]->reroute_url);
    }

    protected function renderErrors(array $moves, array $errors) {
        if (sizeof($errors) < 1) {
            return;
        }
        AuditPost::AmendAuditPostWithErrors($errors);
        $msg = '<ul>';
        foreach ($errors as $err) {
            $msg .= "<li>$err</li>";
        }
        $msg .= '</ul>';
        Flash::instance()->addMessage($msg, 'danger');
        $this->get($this->f3);
        exit();
    }
}
