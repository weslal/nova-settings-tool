<?php

namespace Pkaratanev\NovaSettingsTool\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class SettingValue
 * @package Pkaratanev\NovaSettingsTool\Entities
 */
final class SettingValue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value'
    ];

    /**
     * Get the SettingValues by its key.
     * @param string $key
     * @return Collection
     */
    public static function findByKey(string $key): Collection
    {
        return self::query()->where('key', $key)->get();
    }
}
