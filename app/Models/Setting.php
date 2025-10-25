<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function getIntValue(string $key, int $default = 0)
    {
        $value = self::getValue($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function getBoolValue(string $key, bool $default = false)
    {
        $value = self::getValue($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return strtolower($value) === 'true' || $value === '1';
        }
        return $default;
    }
}
