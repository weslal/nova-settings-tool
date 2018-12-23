<?php

namespace WesLal\NovaSettingsTool\Enums;

use ReflectionClass;
use ReflectionException;

/**
 * Class BaseEnum
 * @package WesLal\NovaSettingsTool\Enums
 */
abstract class BaseEnum {
    /**
     * @var array|null
     */
    private static $constCacheArray = null;

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public static function getConstants()
    {
        if (self::$constCacheArray == null) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    /**
     * @param string $name
     * @param bool $strict
     * @return bool
     * @throws ReflectionException
     */
    public static function isValidName(string $name, bool $strict = false): bool
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    /**
     * @param mixed $value
     * @param bool $strict
     * @return bool
     * @throws ReflectionException
     */
    public static function isValidValue($value, bool $strict = true): bool
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}