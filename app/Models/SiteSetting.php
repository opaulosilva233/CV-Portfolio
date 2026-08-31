<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory, \App\Traits\HasTranslations;

    public function getTranslatableFields()
    {
        return ['value'];
    }

    public function isTranslatable()
    {
        return in_array($this->key, ['job_title', 'bio', 'footer_text', 'seo_title', 'seo_description', 'seo_keywords']);
    }

    protected $fillable = ['key', 'value', 'type', 'group', 'image_data', 'image_mime_type'];

    protected $hidden = ['image_data'];

    /**
     * Get all settings from cache or database
     */
    protected static function getAllCached()
    {
        return Cache::remember('site_settings_models', 3600, function () {
            return self::with('translations')->get()->keyBy('key');
        });
    }

    /**
     * Get a setting value with caching (loads all settings at once)
     */
    public static function getValue($key, $default = null)
    {
        $settings = self::getAllCached();
        
        if (isset($settings[$key])) {
            $setting = $settings[$key];
            return $setting->isTranslatable() ? $setting->translated('value') : $setting->value;
        }
        
        return $default;
    }

    /**
     * Clear the settings cache (call this when settings are updated)
     */
    public static function clearCache(): void
    {
        Cache::forget('site_settings');
        Cache::forget('site_settings_models');
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
