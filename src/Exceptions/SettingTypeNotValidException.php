<?php

namespace Pkaratanev\NovaSettingsTool\Exceptions;

use Exception;

/**
 * Class SettingTypeNotValidException
 * @package Pkaratanev\NovaSettingsTool\Exceptions
 */
final class SettingTypeNotValidException extends Exception
{
    /**
     * SettingTypeNotValidException constructor.
     * @param string $inputType
     */
    public function __construct(string $inputType = "")
    {
        parent::__construct("`{$inputType}` is not a valid SettingType.", 0, null);
    }
}