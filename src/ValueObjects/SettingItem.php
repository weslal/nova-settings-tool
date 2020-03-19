<?php

namespace WesLal\NovaSettingsTool\ValueObjects;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Serializable;
use JsonSerializable;
use Illuminate\Contracts\Container\Container;
use WesLal\NovaSettingsTool\Entities\SettingValue;
use WesLal\NovaSettingsTool\Enums\SettingType;
use WesLal\NovaSettingsTool\Exceptions\SettingTypeNotValidException;
use WesLal\NovaSettingsTool\Traits\CacheableTrait;
use WesLal\NovaSettingsTool\Traits\CallableTrait;
use WesLal\NovaSettingsTool\Traits\JsonableTrait;

/**
 * Class SettingItem
 * @package WesLal\NovaSettingsTool\ValueObjects
 */
final class SettingItem implements Serializable, JsonSerializable
{
    use CallableTrait, CacheableTrait, JsonableTrait;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|SettingType
     */
    private $type = SettingType::TEXT;

    /**
     * @var int
     */
    private $priority = 0;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var Field
     */
    private $field;

    /**
     * @var mixed
     */
    private $value = '';

    /**
     * @var bool
     */
    private $changed = false;

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
        'type',
        'priority',
        'options',
        'field',
        'value'
    ];

    /**
     * Data that should be cached
     * @var array
     */
    protected $jsonables = [
        'key',
        'name',
        'type',
        'priority',
        'options',
        'field',
        'value'
    ];

    /**
     * SettingItem constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->value = $this->getValue();
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key ?? '';
    }

    /**
     * @param string $key
     * @return SettingItem
     */
    public function key(string $key): SettingItem
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return !empty($this->name) ? $this->name : $this->getNameUsingKey();
    }

    /**
     * @param string $name
     * @return SettingItem
     */
    public function name(string $name): SettingItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type|SettingType
     * @return SettingItem
     * @throws SettingTypeNotValidException
     * @throws \ReflectionException
     */
    public function type(string $type): SettingItem
    {
        if (!SettingType::isValidValue($type)) {
            throw new SettingTypeNotValidException($type);
        }
        $this->type = $type;
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
     * @return SettingItem
     */
    public function priority(int $priority): SettingItem
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return SettingItem
     */
    public function options(array $options): SettingItem
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        if (is_null($this->field)) {
            $this->field = $this->getFieldByType($this->type);
        }
        $this->field->name = $this->getName();
        $this->field->value = $this->getValue();
        return $this->field;
    }

    /**
     * @param Field $field
     * @return SettingItem
     */
    public function field(Field $field): SettingItem
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @param bool $forceFromDatabase
     * @param bool $isObjectOrArray
     * @return mixed
     */
    public function getValue(bool $forceFromDatabase = false, bool $isObjectOrArray = false)
    {
        $keyNotValid = is_null($this->key) || empty($this->key) || !is_string($this->key);
        $valueNotValid = is_null($this->value) || empty($this->value);
        if (!$keyNotValid || !$valueNotValid || !$forceFromDatabase) {
            if ($this->getKey()) {
                $collection = SettingValue::findByKey($this->getKey());
                if ($collection->count() > 0) {
                    $this->value = $collection->first()->value;
                }
            }
        }

        if (is_null($this->value) || empty($this->value)) {
            if (isset($this->options['default'])) {
                $this->value = $this->options['default'];
            }
        }

        if (is_null($this->value) || empty($this->value)) {
            $this->value = null;
        }

        if ($isObjectOrArray === true) {
            $this->value = json_decode($this->value);
        }

        return $this->value;
    }

    /**
     * @param mixed $value
     * @param bool $save
     * @return SettingItem
     */
    public function value($value, bool $save = true): SettingItem
    {
        $this->changed = $this->value !== $value;
        $this->value = $value;

        if ($save === true) {
            $this->save();
        }

        return $this;
    }

    /**
     * Saves the value to the database.
     * @return SettingItem
     */
    public function save(): SettingItem
    {
        if ($this->changed === true && !empty($this->getKey())) {
            $collection = SettingValue::findByKey($this->key);
            if ($collection->count() > 0) {
                $value = $this->value;
                if (is_object($value) || is_array($value)) {
                    $value = json_encode($value);
                }
                if (!is_null($this->value)) {
                    $collection->first()->value = $value;
                    $collection->first()->save();
                    $this->changed = false;
                }
            } else {
                if (is_null($this->value)) {
                    $this->setDefaultValue();
                }

                if (!is_null($this->value)) {
                    $item = new SettingValue();
                    $item->key = $this->getKey();
                    $item->value = $this->value;
                    $item->save();
                }
            }
        }

        return $this;
    }

    /**
     * Set default value as value
     * @param bool $save
     * @return SettingItem
     */
    private function setDefaultValue(bool $save = false): SettingItem
    {
        $this->value = isset($this->options['default']) ? $this->options['default'] : '';
        if ($save === true) {
            $this->save();
        }
        return $this;
    }

    /**
     * @return string
     */
    private function getNameUsingKey() : string
    {
        return title_case(str_replace('_', ' ',snake_case($this->getKey())));
    }

    /**
     * @param string $type
     * @return Boolean|Code|Date|DateTime|Number|Text|Textarea|Password
     */
    private function getFieldByType(string $type)
    {
        $key = $this->getKey();
        switch ($type) {
            case SettingType::TEXT: $field = Text::make($key); $field->name = $this->getName(); return $field;
            case SettingType::BOOLEAN: $field = Boolean::make($key); $field->name = $this->getName(); return $field;
            case SettingType::NUMBER: $field = Number::make($key); $field->name = $this->getName(); return $field;
            case SettingType::TEXTAREA: $field = Textarea::make($key); $field->name = $this->getName(); return $field;
            case SettingType::DATE: $field = Date::make($key); $field->name = $this->getName(); return $field;
            case SettingType::DATETIME: $field = DateTime::make($key); $field->name = $this->getName(); return $field;
            case SettingType::CODE: $field = Code::make($key); $field->name = $this->getName(); return $field;
            case SettingType::PASSWORD: $field = Password::make($key); $field->name = $this->getName(); return $field;
            default: return Text::make($key);
        }
    }
}
