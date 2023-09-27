<?php

namespace GeoKrety\Service;

use GeoKrety\Model\User;
use GeoKrety\Model\UsersSettings;
use GeoKrety\Model\UsersSettingsParameters;
use Sugar\Event;

/**
 * Manage user settings. Allows read and write.
 */
class UserSettings extends \Prefab {
    /**
     * Create or update a user personal setting.
     *
     * @param \GeoKrety\Model\User|int $user
     *
     * @throws \Exception
     */
    public function put($user, string $setting_name, $value): bool {
        $setting = new UsersSettings();
        $setting->load(['user = ? AND name = ?', gettype($user) === 'GeoKrety\Model\User' ? $user->id : $user, $setting_name]);
        $setting->name = $setting_name;
        $setting->user = $user;
        try {
            $setting->value = $value;
        } catch (\GeoKrety\Model\NoSuchSettingException $e) {
            return false;
        }

        if (!$setting->validate()) {
            return false;
        }

        if ($setting->save() === false) {
            Event::instance()->emit('user.setting.save.failed', $setting);

            return false;
        }

        Event::instance()->emit('user.setting.save.success', $setting);
        \Base::instance()->set('SESSION.SETTINGS.'.$setting->getRaw('name'), $setting->value);

        return true;
    }

    /**
     * @throws \Exception
     */
    public static function putForCurrentUser(string $setting_name, $value) {
        $f3 = \Base::instance();
        $user_settings = new UserSettings();

        return $user_settings->put($f3->get('SESSION.CURRENT_USER'), $setting_name, $value);
    }

    /**
     * Get a user custom setting or the default site value.
     *
     * @return string|int|bool|null
     */
    public function get(User|int|null $user, string $setting_name) {
        if (is_null($user)) {
            return $this->getDefault($setting_name);
        }
        $f3 = \Base::instance();
        if (!$f3->exists('SESSION.SETTINGS')) {
            $this->loadUserSettings($user);
        }
        if ($f3->exists('SESSION.SETTINGS.'.$setting_name)) {
            return $f3->get('SESSION.SETTINGS.'.$setting_name);
        }

        return $this->getDefault($setting_name);
    }

    /**
     * Get a setting for the currently connected user.
     *
     * @return bool|int|string|null
     */
    public static function getForCurrentUser(string $setting_name) {
        $f3 = \Base::instance();
        $user_settings = new UserSettings();

        return $user_settings->get($f3->get('SESSION.CURRENT_USER'), $setting_name);
    }

    /**
     * Get the default site value for a setting name.
     *
     * @return string|int|bool|null
     */
    public function getDefault(string $setting_name) {
        $users_settings_parameters = new UsersSettingsParameters();
        if (!$users_settings_parameters->load(['name = ?', $setting_name], ttl: 0)) {
            throw new \GeoKrety\Model\NoSuchSettingException("Setting '$setting_name' doesn't exist");
        }

        return $users_settings_parameters->default;
    }

    /**
     * Load custom user's settings.
     */
    protected function loadUserSettings(User|int|null $user): void {
        // Declare settings as loaded
        $f3 = \Base::instance();
        $f3->set('SESSION.SETTINGS', []);
        $users_settings = new UsersSettings();
        $settings = $users_settings->find(['user = ?', gettype($user) === 'GeoKrety\Model\User' ? $user->id : $user]);
        if (!$settings) {
            return;
        }
        foreach ($settings as $setting) {
            $f3->set('SESSION.SETTINGS.'.$setting->getRaw('name'), $setting->value);
        }
    }
}
