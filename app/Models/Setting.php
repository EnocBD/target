<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'value',
        'type',
        'group',
        'description'
    ];

    protected $casts = [
        'value' => 'string'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $name = null, $type = 'text', $group = 'general', $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
            ]
        );

        return $setting;
    }

    /**
     * Get all settings grouped
     */
    public static function getAllGrouped()
    {
        return static::orderBy('group')->orderBy('name')->get()->groupBy('group');
    }

    /**
     * Get display name (use name if available, fallback to key)
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: $this->key;
    }
}