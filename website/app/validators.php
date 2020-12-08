<?php

// Initialize the validator with custom rules
$validator = \Validation::instance();

$validator->onError(function ($text, $key) {
    \Base::instance()->push('validation.error', $text);
    \Flash::instance()->addMessage($text, 'danger');
});

$validator->addValidator('not_empty', function ($field, $input, $param = null) {
    return \GeoKrety\Validation\Base::isNotEmpty($input[$field]);
}, _('The {0} field cannot be empty'));

$validator->addValidator('is_date', function ($field, $input, $param = null) {
    return is_a($input[$field], 'DateTime');
}, _('The {0} field must be a valid date time'));

$validator->addValidator('geokrety_type', function ($field, $input, $param = null) {
    return \GeoKrety\GeokretyType::isValid($input[$field]->getTypeId());
}, _('The GeoKret type is invalid'));

$validator->addValidator('log_type', function ($field, $input, $param = null) {
    return \GeoKrety\LogType::isValid($input[$field]->getLogTypeId());
}, _('The move type is invalid'));

$validator->addValidator('picture_type', function ($field, $input, $param = null) {
    return \GeoKrety\PictureType::isValid($input[$field]->getTypeId());
}, _('The picture type is invalid'));

$validator->addValidator('language_supported', function ($field, $input, $param = null) {
    return \GeoKrety\Service\LanguageService::isLanguageSupported($input[$field]);
}, _('This language is not supported'));

$validator->addValidator('ciphered_password', function ($field, $input, $param = null) {
    $expectedPasswordPrefix = sprintf('$2a$%02d$', GK_PASSWORD_HASH_ROTATION);

    return substr($input[$field], 0, 7) === $expectedPasswordPrefix;
}, _('The password must be ciphered'));

$validator->addValidator('anonymous_only_required', function ($field, $input, $param = null) {
    $f3 = \Base::instance();
    if (!$f3->get('SESSION.CURRENT_USER') && \GeoKrety\Validation\Base::isEmpty($input[$field])) {
        return false;
    }

    return true;
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
    if ($input['move_type']->isCoordinatesRequired() && strlen($input[$field])) {
        return true;
    }
    if (!$input['move_type']->isCoordinatesRequired() && !strlen($input[$field])) {
        return true;
    }

    return false;
}, _('This logtype require valid {0} coordinates'));

$validator->addValidator('move_not_same_datetime', function ($field, $input, $param = null) {
    if (!$input[$field] or is_null($input['geokret'])) {
        return true;
    }

    $move = new  \GeoKrety\Model\Move();
    $move->load([$field.' = ? AND geokret = ? AND id != ?', $input[$field]->format(GK_DB_DATETIME_FORMAT), $input['geokret']->id, $input['_id']]);

    return $move->dry();
}, _('Something already exists at the same datetime "{0}"'));

$validator->addValidator('not_in_the_future', function ($field, $input, $param = null) {
    if (!$input[$field]) {
        return true;
    }

    return $input[$field] <= new DateTime();
}, _('{0} cannot be in the future'));

$validator->addValidator('after_geokret_birth', function ($field, $input, $param = null) {
    if (!$input[$field] or is_null($input['geokret'])) {
        return true;
    }

    return $input[$field]->format('Y-m-d H:i') >= $input['geokret']->created_on_datetime->format('Y-m-d H:i');
}, _('{0} must be after GeoKret birth'));

$validator->addValidator('email_activation_require_previous_email_field', function ($field, $input, $param = null) {
    return in_array($input['used'], \GeoKrety\Model\EmailActivationToken::TOKEN_NEED_PREVIOUS_EMAIL_FIELD, true);
}, '{0} require update fields');

$validator->addValidator('email_activation_require_update', function ($field, $input, $param = null) {
    return in_array($input['used'], \GeoKrety\Model\EmailActivationToken::TOKEN_NEED_UPDATE, true);
}, '{0} require update fields');

$validator->addValidator('email_activation_require_revert', function ($field, $input, $param = null) {
    return in_array($input['used'], \GeoKrety\Model\EmailActivationToken::TOKEN_NEED_REVERT, true);
}, '{0} require revert fields');

$validator->addValidator('account_activation_require_validate', function ($field, $input, $param = null) {
    return in_array($input['used'], \GeoKrety\Model\AccountActivationToken::TOKEN_NEED_VALIDATE, true);
}, '{0} require validate fields');

$validator->addValidator('is_not_false', function ($field, $input, $param = null) {
    return $input[$field] !== false;
}, 'Invalid value for {0}');

$validator->addFilter('HTMLPurifier', function ($value, $params = null) {
    return \GeoKrety\Service\HTMLPurifier::getPurifier()->purify($value);
});

$validator->addFilter('EmptyString2Null', function ($value, $params = null) {
    return empty($value) ? null : $value;
});

$validator->loadLang();
