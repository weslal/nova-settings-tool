<?php
if (!function_exists('settings')) {
    function settings() {
        \WesLal\NovaSettingsTool\ValueObjects\SettingRegister::getInstance();
    }
}

if (!function_exists('setting')) {
    function setting(string $key) {
        return \WesLal\NovaSettingsTool\ValueObjects\SettingRegister::getSettingItem($key);
    }
}

if (!function_exists('settingValue')) {
    function settingValue(string $key) {
        $settingValue = \WesLal\NovaSettingsTool\Entities\SettingValue::findByKey($key);
        if ($settingValue->count() > 0) {
            return $settingValue->first()->value;
        }
        return null;
    }
}