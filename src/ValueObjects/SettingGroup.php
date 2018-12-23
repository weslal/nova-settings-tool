<?php

namespace WesLal\NovaSettingsTool\ValueObjects;

use Illuminate\Http\Resources\MergeValue;
use Serializable;
use Closure;
use JsonSerializable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use WesLal\NovaSettingsTool\Traits\CacheableTrait;
use WesLal\NovaSettingsTool\Traits\CallableTrait;
use WesLal\NovaSettingsTool\Traits\JsonableTrait;

/**
 * Class SettingGroup
 * @package WesLal\NovaSettingsTool\ValueObjects
 */
final class SettingGroup implements Serializable, JsonSerializable
{
    use CallableTrait, CacheableTrait, JsonableTrait;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var Collection|SettingItem[]
     */
    protected $items;

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Data that should be cached
     * @var array
     */
    protected $cacheables = [
        'key',
        'name',
        'items',
        'icon',
        'priority'
    ];

    /**
     * Data that should be cached
     * @var array
     */
    protected $jsonables = [
        'key',
        'name',
        'items',
        'icon',
        'priority'
    ];

    /**
     * SettingGroup constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->items        = new Collection();
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return SettingGroup
     */
    public function key(string $key): SettingGroup
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return !empty($this->name) ? $this->name : $this->getKey();
    }

    /**
     * @param string $name
     * @return SettingGroup
     */
    public function name(string $name): SettingGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return SettingGroup
     */
    public function icon(string $icon): SettingGroup
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return SettingGroup
     */
    public function priority(int $priority): SettingGroup
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Add a new SettingItem (or edit an existing SettingItem) to the SettingGroup
     * @param string $key
     * @param Closure|null $callback
     * @return SettingGroup
     * @throws \ReflectionException
     */
    public function item(string $key, Closure $callback = null): SettingGroup
    {
        if ($this->items->has($key)) {
            $item = $this->items->get($key);
        } else {
            $item = $this->container->make(SettingItem::class);
            $item->key($key);
        }

        $this->call($callback, $item);

        $this->addItem($item);

        return $this;
    }

    /**
     * Add SettingItem instance to the SettingGroup
     * @param SettingItem $item
     * @return SettingGroup
     */
    public function addItem(SettingItem $item): SettingGroup
    {
        $this->items->put($item->getKey(), $item);
        return $this;
    }

    /**
     * Get the SettingItems from the SettingGroup
     * @return Collection|SettingItem[]
     */
    public function getItems(): Collection
    {
        return $this->items->sortByDesc(function (SettingItem $items) {
            return $items->getPriority();
        });
    }

    /**
     * Check if the SettingGroup has SettingItems
     * @return bool
     */
    public function hasItems(): bool
    {
        return count($this->items) > 0 ? true : false;
    }

    /**
     * Save all changed values of the SettingItems.
     * @return SettingGroup
     */
    public function saveAllChangedValues(): SettingGroup
    {
        foreach ($this->items as $item) {
            $item->save();
        }

        return $this;
    }
}
