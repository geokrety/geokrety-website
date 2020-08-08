<?php

namespace GeoKrety\Model;

use DB\SQL\Schema;

/**
 * @property mixed id
 * @property int|User user Connected user
 * @property int|SocialAuthProvider provider Connected provider
 * @property string uid
 */
class UserSocialAuth extends Base implements \JsonSerializable {
    use \Validation\Traits\CortexTrait;

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

    public function jsonSerialize() {
        return [
            'id' => $this->id,
//            'user' => $this->user,
//            'provider' => $this->provider,
//            'uid' => $this->uid,
        ];
    }
}
