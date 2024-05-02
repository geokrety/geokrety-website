<?php

namespace GeoKrety\Model;

/**
 * @property int|User user Connected user
 * @property string|UsersSettingsParameters name
 */
class CustomUsersSettings extends BaseCustomSettings {
    protected $table = 'gk_users_settings';

    protected $fieldConfAppend = [
        'user' => [
            'belongs-to-one' => '\GeoKrety\Model\User',
            'validate' => 'required',
            'nullable' => false,
        ],
        'name' => [
            'belongs-to-one' => '\GeoKrety\Model\UsersSettingsParameters',
            'validate' => 'required',
            'nullable' => false,
        ],
    ];

    public function jsonSerialize(): mixed {
        return array_merge(['user' => $this->getRaw('user')], parent::jsonSerialize());
    }
}
