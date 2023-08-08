<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;
use Sugar\Event;
use Validation\Traits\CortexTrait;

/**
 * @property mixed id
 * @property int|User user Connected user
 * @property int|SocialAuthProvider provider Connected provider
 * @property string uid
 */
class UserSocialAuth extends Base implements \JsonSerializable {
    use CortexTrait;

    protected $db = 'DB';
    protected $table = 'gk_users_social_auth';

    protected $fieldConf = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
            'nullable' => false,
        ],
        'provider' => [
            'belongs-to-one' => '\GeoKrety\Model\SocialAuthProvider',
            'validate' => 'required',
            'nullable' => false,
        ],
        'uid' => [
            'type' => Schema::DT_TEXT,
            'validate' => 'required',
            'nullable' => false,
        ],
    ];

    public function __construct() {
        parent::__construct();
        $this->afterinsert(function ($self) {
            Event::instance()->emit('user.oauth.attach', $self);
            \Flash::instance()->addMessage(sprintf(_('Your may now use your %s account to authenticate on GeoKrety.'), $self->provider->name), 'success');
        });
        $this->aftererase(function ($self) {
            Event::instance()->emit('user.oauth.detach', $self);
            \Flash::instance()->addMessage(sprintf(_('Your account has been detached from %s.'), $self->provider->name), 'success');
        });
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'user' => $this->user->id,
            'username' => $this->user->username,
            'provider' => $this->provider->id,
            'provider_name' => $this->provider->name,
            // 'uid' => $this->uid,
        ];
    }
}
