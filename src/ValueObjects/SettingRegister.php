<?php

namespace Pkaratanev\NovaSettingsTool\ValueObjects;

use Closure;
use Serializable;
use JsonSerializable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Pkaratanev\NovaSettingsTool\Events\SettingsUpdated;
use Pkaratanev\NovaSettingsTool\Events\SettingsRegistering;
use Pkaratanev\NovaSettingsTool\Traits\CacheableTrait;
use Pkaratanev\NovaSettingsTool\Traits\CallableTrait;
use Pkaratanev\NovaSettingsTool\Traits\JsonableTrait;
use ReflectionException;

/**
 * Class SettingRegister
 * @package Pkaratanev\NovaSettingsTool\ValueObjects
 */
final class SettingRegister implements Serializable, JsonSerializable
{
    use CallableTrait, CacheableTrait, JsonableTrait;

    /**
     * @var Collection|SettingGroup[]
     */
    protected $groups;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Data that should be cached
     * @var array
     */
    protected $cacheables = [
        'groups',
    ];

    /**
     * Data that should be serialized when encoding to JSON
     * @var array
     */
    protected $jsonables = [
        'groups',
    ];

    /**
     * SettingRegister constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->groups = new Collection();
        event(new SettingsRegistering($this));
    }

    /**
     * Get collection of SettingGroup instances sorted by their priority
     * @return Collection|SettingGroup[]
     */
    public function getGroups(): Collection
    {
        return $this->groups->sortByDesc(function (SettingGroup $group) {
            return $group->getPriority();
        });
    }

    /**
     * Get SettingGroup instance by it's key
     * @param string $key
     * @return SettingGroup
     */
    public function getGroup(string $key): SettingGroup
    {
        if ($this->groups->has($key)) {
            return $this->groups->get($key);
        } else {
            $group = $this->container->make(SettingGroup::class);
            $group->key($key);

            return $group;
        }
    }

    /**
     * Add a SettingGroup instance to the SettingRegister
     * @param SettingGroup $group
     * @return SettingRegister
     */
    public function addGroup(SettingGroup $group): SettingRegister
    {
        $this->groups->put($group->getKey(), $group);

        return $this;
    }

    /**
     * Remove a SettingGroup from the SettingRegister.
     * @param string $key
     * @return SettingRegister
     */
    public function removeGroup(string $key): SettingRegister
    {
        if ($this->groups->has($key)) {
            $this->groups->forget($key);
        }

        return $this;
    }

    /**
     * Init a new SettingGroup or call an existing SettingGroup and add it to the SettingRegister
     * @param string $key
     * @param Closure|null $callback
     * @return SettingRegister
     * @throws ReflectionException
     */
    public function group(string $key, Closure $callback = null): SettingRegister
    {
        if ($this->groups->has($key)) {
            $group = $this->groups->get($key);
        } else {
            $group = $this->container->make(SettingGroup::class);
            $group->key($key);
        }

        $this->call($callback, $group);

        $this->addGroup($group);

        return $this;
    }

    /**
     * Save all changed values of the SettingItems within the SettingGroups.
     * @return SettingRegister
     */
    public function saveAllChangedValues(): SettingRegister
    {
        foreach ($this->groups as $group) {
            $group->saveAllChangedValues();
        }

        return $this;
    }

    /**
     * Update the values of multiple SettingItems.
     * @param array $keyValueArray
     * @param bool $saveInBetween
     * @return SettingRegister
     */
    public function massUpdate(array $keyValueArray, bool $saveInBetween = false): SettingRegister
    {
        foreach ($this->groups as $group) {
            foreach ($group->getItems() as $item) {
                foreach ($keyValueArray as $key => $value) {
                    if ($key === $item->getKey()) {
                        $item->value($value ?? '', $saveInBetween);
                    }
                }
            }
        }

        if (!$saveInBetween) {
            $this->saveAllChangedValues();
            event(new SettingsUpdated($keyValueArray));
        }

        return $this;
    }

    /**
     * Init the SettingRegister.
     */
    public static function init()
    {
        if (!isset(app()['settings']) && self::checkInstance() === false) {
            $settings = new SettingRegister(app());
            app()->instance('settings', $settings);
        }
    }

    /**
     * Get the instance from the app container.
     * @return SettingRegister
     */
    public static function getInstance(): SettingRegister
    {
        self::init();

        return app()['settings'];
    }

    /**
     * Check if the instance in the app container is of the type SettingRegister type.
     * @return bool
     */
    protected static function checkInstance(): bool
    {
        if (isset(app()['settings']) && app()['settings'] instanceof SettingRegister) {
            return true;
        }

        return false;
    }

    /**
     * Get the SettingItem instance by key from the app container.
     * @return mixed|null|SettingItem
     * @var string $key
     */
    public static function getSettingItem(string $key)
    {
        $settingsRegister = self::getInstance();
        $groups = $settingsRegister->getGroups();
        $items = new Collection();
        foreach ($groups as $group) {
            $items = $items->merge($group->getItems());
        }
        foreach ($items as $item) {
            if ($item->getKey() === $key) {
                return $item;
            }
        }

        return null;
    }
}
