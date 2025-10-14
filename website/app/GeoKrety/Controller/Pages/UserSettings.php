<?php

namespace GeoKrety\Controller\Pages;

use GeoKrety\Model\CustomUsersSettings;
use GeoKrety\Model\UsersSettingsParameters;
use GeoKrety\Service\Smarty;
use GeoKrety\Service\UserSettings as UserSettingsService;
use GeoKrety\Traits\CurrentUserLoader;

class UserSettings extends \GeoKrety\Controller\Base {
    use CurrentUserLoader;

    public function get(\Base $f3) {
        // Load all available settings parameters
        $settingsParameters = new UsersSettingsParameters();
        $allParameters = $settingsParameters->find([], ['order' => 'name ASC']);

        // Load current user's custom settings
        $customSettings = new CustomUsersSettings();
        $userCustomSettings = $customSettings->find(['user = ?', $f3->get('SESSION.CURRENT_USER')]);

        // Build a map of custom values
        $customValuesMap = [];
        foreach ($userCustomSettings ?: [] as $setting) {
            $customValuesMap[$setting->getRaw('name')] = $setting->value;
        }

        // Prepare data structure for the page
        $settingsData = [];

        foreach ($allParameters as $param) {
            $currentValue = isset($customValuesMap[$param->name])
                ? $customValuesMap[$param->name]
                : $param->default;

            $settingsData[] = [
                'name' => $param->name,
                'description' => $param->description ?: '',
                'type' => $param->type,
                'default' => $param->default,
                'current' => $currentValue,
                'is_customized' => isset($customValuesMap[$param->name]),
            ];
        }

        Smarty::assign('settingsData', $settingsData);
        Smarty::render('pages/user_settings.tpl');
    }

    public function dialog_edit_get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_setting_edit.tpl');
    }

    public function dialog_edit_get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_setting_edit.tpl');
    }

    public function dialog_reset_get(\Base $f3) {
        Smarty::render('extends:full_screen_modal.tpl|dialog/user_setting_reset.tpl');
    }

    public function dialog_reset_get_ajax(\Base $f3) {
        Smarty::render('extends:base_modal.tpl|dialog/user_setting_reset.tpl');
    }

    public function post(\Base $f3) {
        if (!$f3->exists('POST')) {
            return;
        }

        $errors = [];

        foreach ($f3->get('POST') as $setting_name => $setting_value) {
            // Get the default value for this setting
            try {
                $settingsParameter = new UsersSettingsParameters();
                $settingsParameter->load(['name = ?', $setting_name]);

                // Normalize both values to strings for comparison (since POST data is always strings)
                // getRaw('default') returns the string representation from database
                $normalizedDefault = (string) $settingsParameter->getRaw('default');
                $normalizedValue = (string) $setting_value;

                // If value equals default, delete the custom setting
                if ($normalizedValue === $normalizedDefault) {
                    $customSetting = new CustomUsersSettings();
                    $customSetting->load(['user = ? AND name = ?', $f3->get('SESSION.CURRENT_USER'), $setting_name]);

                    if (!$customSetting->dry()) {
                        $customSetting->erase();
                    }

                    // Clear session cache
                    $f3->clear("SESSION.SETTINGS.$setting_name");
                } else {
                    // Otherwise, save the custom value
                    if (UserSettingsService::putForCurrentUser($setting_name, $setting_value) === false) {
                        $errors[] = sprintf(_('Failed to save setting "%s"'), $setting_name);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = sprintf(_('Failed to save setting "%s"'), $setting_name);
            }
        }

        if (sizeof($errors)) {
            $f3->status(400);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(sizeof($errors) ? $errors : null);
    }
}
