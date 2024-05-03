<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

/**
 * @property string requesting_ip
 * @property string|null validating_ip
 */
class AccountActivationToken extends TokenBase {
    public const TOKEN_DAYS_VALIDITY = GK_SITE_ACCOUNT_ACTIVATION_CODE_DAYS_VALIDITY;

    protected $table = 'gk_account_activation';

    protected $fieldConfAppend = [
        'requesting_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => false,
            'validate' => 'valid_ip',
        ],
        'validating_ip' => [
            'type' => Schema::DT_VARCHAR128,
            'nullable' => true,
            'validate' => 'valid_ip',
            'validate_depends' => [
                'used' => ['validate', 'account_activation_require_validate'],
            ],
        ],
    ];

    public function __construct() {
        parent::__construct();
        $this->beforeinsert(function ($self) {
            $self->requesting_ip = \Base::instance()->get('IP');
        });

        $this->afterinsert(function ($self) {
            \Sugar\Event::instance()->emit('activation.token.created', $self);
        });
    }
}
