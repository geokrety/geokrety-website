<?php

namespace GeoKrety\Service;

use GeoKrety\Model\CustomSiteSettings;
use GeoKrety\Model\SiteSettingsParameters;
use GeoKrety\Model\User;
use Sugar\Event;

/**
 * Manage user settings. Allows read and write.
 */
class SiteSettings extends \Prefab {
    private $setting;

    public function __construct() {
        $this->setting = new CustomSiteSettings();
    }

    /**
     * Create or update a site setting.
     *
     * @throws \Exception
     */
    public function put(string $setting_name, $value): bool {
        $this->setting->load(['name = ?', $setting_name]);
        $this->setting->name = $setting_name;
        $this->setting->value = $value;

        if (!$this->setting->validate()) {
            return false;
        }

        if ($this->setting->save() === false) {
            Event::instance()->emit('site.setting.save.failed', $this->setting);

            return false;
        }

        Event::instance()->emit('site.setting.save.success', $this->setting);

        return true;
    }

    /**
     * Get a custom setting or the default value.
     *
     * @return string|int|bool|null
     */
    public function get(string $setting_name) {
        $this->setting->load(['name = ?', $setting_name], ttl: 60);
        if ($this->setting->dry()) {
            return $this->getDefault($setting_name);
        }

        return $this->setting->name->convertValueToSettingType($this->setting->value);
    }

    /**
     * Get the default site value for a setting name.
     *
     * @return string|int|bool|null
     */
    public function getDefault(string $setting_name) {
        $ss_parameters = new SiteSettingsParameters();
        if (!$ss_parameters->load(['name = ?', $setting_name], ttl: 600)) {
            throw new \GeoKrety\Model\NoSuchSettingException("Site setting '$setting_name' doesn't exist");
        }

        return $ss_parameters->default;
    }
}
