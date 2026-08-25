<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsPortfolioCache
{
    /**
     * Boot the trait to clear cache on save and delete.
     */
    protected static function bootClearsPortfolioCache()
    {
        static::saved(function () {
            static::clearPortfolioCache();
        });

        static::deleted(function () {
            static::clearPortfolioCache();
        });
    }

    /**
     * Clear all portfolio-related caches.
     */
    public static function clearPortfolioCache()
    {
        Cache::forget('portfolio_projects');
        Cache::forget('portfolio_skills');
        Cache::forget('portfolio_experiences');
        Cache::forget('portfolio_educations');
        Cache::forget('portfolio_interests');
        Cache::forget('portfolio_sections');
    }
}
