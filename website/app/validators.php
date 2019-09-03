<?php

// Initialize the validator with custom rules
$validator = \Validation::instance();

$validator->onError(function ($text, $key) {
    \Flash::instance()->addMessage($text, 'danger');
});

$validator->addValidator('not_empty', function ($field, $input, $param = null) {
    return \GeoKrety\Validation\Base::isNotEmpty($input[$field]);
}, _('The {0} field cannot be empty'));

$validator->addValidator('geokrety_type', function ($field, $input, $param = null) {
    return \GeoKrety\GeokretyType::isValid($input[$field]->getTypeId());
}, _('The GeoKret type is invalid'));

$validator->addValidator('log_type', function ($field, $input, $param = null) {
    return \GeoKrety\LogType::isValid($input[$field]->getLogTypeId());
}, _('The move type is invalid'));

$validator->addValidator('language_supported', function ($field, $input, $param = null) {
    return \GeoKrety\Service\LanguageService::isLanguageSupported($input[$field]);
}, _('This language is not supported'));

$validator->addValidator('ciphered_password', function ($field, $input, $param = null) {
    return substr($input[$field], 0, 7) === '$2a$11$';
}, _('The password must be ciphered'));

$validator->addValidator('anonymous_only_required', function ($field, $input, $param = null) {
    $f3 = \Base::instance();
    if ($f3->get('SESSION.CURRENT_USER') && \GeoKrety\Validation\Base::isEmpty($input[$field])) {
        return true;
    }
    if (!$f3->get('SESSION.CURRENT_USER') && \GeoKrety\Validation\Base::isNotEmpty($input[$field])) {
        return true;
    }

    return false;
}, _('Anonymous users must provide a value for {0}'));

$validator->addValidator('registered_only_required', function ($field, $input, $param = null) {
    $f3 = \Base::instance();
    if ($f3->get('SESSION.CURRENT_USER') && !empty($input[$field])) {
        return true;
    }
    if (!$f3->get('SESSION.CURRENT_USER') && empty($input[$field])) {
        return true;
    }

    return false;
}, _('Registered users must provide a value for {0}'));

$validator->addValidator('logtype_require_coordinates', function ($field, $input, $param = null) {
    if ($input['logtype']->isCoordinatesRequired() && strlen($input[$field])) {
        return true;
    }
    if (!$input['logtype']->isCoordinatesRequired() && !strlen($input[$field])) {
        return true;
    }

    return false;
}, _('This logtype require valid {0} coordinates'));

$validator->addValidator('move_not_same_datetime', function ($field, $input, $param = null) {
    $move = new  \GeoKrety\Model\Move();
    $move->load(array($field.' = ? AND geokret = ? AND id != ?', $input[$field]->format('Y-m-d H:i:s'), $input['geokret']->id, $input['_id']));

    return $move->dry();
}, _('Something already exists at the same datetime "{0}"'));

$validator->addValidator('not_in_the_future', function ($field, $input, $param = null) {
    return $input[$field] <= new DateTime();
}, _('{0} cannot be in the future'));

$validator->addValidator('after_geokret_birth', function ($field, $input, $param = null) {
    return $input[$field]->format('Y-m-d H:i') >= $input['geokret']->created_on_datetime->format('Y-m-d H:i');
}, _('{0} must be after GeoKret birth'));

$validator->addValidator('email_activation_require_update', function ($field, $input, $param = null) {
    return in_array($input['used'], \GeoKrety\Model\EmailActivation::TOKEN_NEED_UPDATE, true);
}, '{0} require update fileds');

$validator->addValidator('email_activation_require_revert', function ($field, $input, $param = null) {
    return in_array($input['used'], \GeoKrety\Model\EmailActivation::TOKEN_NEED_REVERT, true);
}, '{0} require revert fileds');

$validator->addFilter('HTMLPurifier', function ($value, $params = null) {
    return \GeoKrety\Service\HTMLPurifier::getPurifier()->purify($value);
});

$validator->loadLang();
