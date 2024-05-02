<?php

namespace GeoKrety\Model;

/**
 * @property string|SiteSettingsParameters name
 */
class CustomSiteSettings extends BaseCustomSettings {
    protected $table = 'gk_site_settings';

    protected $fieldConfAppend = [
        'name' => [
            'belongs-to-one' => '\GeoKrety\Model\SiteSettingsParameters',
            'validate' => 'required',
            'nullable' => false,
        ],
    ];
}
