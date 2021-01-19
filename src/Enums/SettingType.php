<?php

namespace Pkaratanev\NovaSettingsTool\Enums;

/**
 * Class SettingType
 * @package Pkaratanev\NovaSettingsTool\Enums
 */
abstract class SettingType extends BaseEnum
{
    /**
     * Boolean Field
     */
    public const BOOLEAN    = 'boolean';

    /**
     * Text Field
     */
    public const TEXT       = 'text';

    /**
     * Number Field
     */
    public const NUMBER     = 'number';

    /**
     * TextArea Field
     */
    public const TEXTAREA   = 'textarea';

    /**
     * Code Field
     */
    public const CODE       = 'code';

    /**
     * Date Field
     */
    public const DATE       = 'date';

    /**
     * DateTime Field
     */
    public const DATETIME   = 'datetime';

    /**
     * Password Field
     */
    public const PASSWORD   = 'password';
}
