<?php

namespace WesLal\NovaSettingsTool\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class SettingsRegistering
 * @package WesLal\NovaSettingsTool\Events
 */
final class SettingsUpdated
{
    use SerializesModels;

    /**
     * @var array
     */
    public $values;

    /**
     * Create a new SettingRegister instance.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
