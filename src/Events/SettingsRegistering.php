<?php

namespace Pkaratanev\NovaSettingsTool\Events;

use Pkaratanev\NovaSettingsTool\ValueObjects\SettingRegister;
use Illuminate\Queue\SerializesModels;

/**
 * Class SettingsRegistering
 * @package Pkaratanev\NovaSettingsTool\Events
 */
final class SettingsRegistering
{
    use SerializesModels;

    /**
     * @var SettingRegister
     */
    public $settingRegister;

    /**
     * Create a new SettingRegister instance.
     *
     * @param  SettingRegister  $settingRegister
     * @return void
     */
    public function __construct(SettingRegister $settingRegister)
    {
        $this->settingRegister = $settingRegister;
    }
}
