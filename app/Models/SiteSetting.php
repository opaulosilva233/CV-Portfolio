<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Get all settings from cache or database
     */
    protected static function getAllCached(): array
    {
        return Cache::remember('site_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a setting value with caching (loads all settings at once)
     */
    public static function getValue($key, $default = null)
    {
        $settings = self::getAllCached();
        return $settings[$key] ?? $default;
    }

    /**
     * Clear the settings cache (call this when settings are updated)
     */
    public static function clearCache(): void
    {
        Cache::forget('site_settings');
    }

    /**
     * Boot method to clear cache when settings are modified
     */
    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
