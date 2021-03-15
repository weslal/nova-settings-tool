<?php
if (!function_exists('settings')) {
    function settings() {
        return \Pkaratanev\NovaSettingsTool\ValueObjects\SettingRegister::getInstance();
    }
}

if (!function_exists('setting')) {
    function setting(string $key) {
        return \Pkaratanev\NovaSettingsTool\ValueObjects\SettingRegister::getSettingItem($key);
    }
}

if (!function_exists('settingValue')) {
    function settingValue(string $key, $default = null) {
        $settingValue = \Pkaratanev\NovaSettingsTool\Entities\SettingValue::findByKey($key);
        if ($settingValue->count() > 0) {
            return $settingValue->first()->value;
        }
        return $default;
    }
}