<?php

namespace WesLal\NovaSettingsTool\Traits;

/**
 * Trait JsonableTrait
 * @package WesLal\NovaSettingsTool\Traits
 */
trait JsonableTrait
{
    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $jsonables = [];
        foreach ($this->getJsonables() as $jsonable) {
            $jsonables[$jsonable] = $this->{'get' . camel_case($jsonable)}();
        }
        return $jsonables;
    }

    /**
     * @return array
     */
    public function getJsonables(): array
    {
        return isset($this->jsonables) ? $this->jsonables : [];
    }
}