<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    /** @use HasFactory<\Database\Factories\ExperienceFactory> */
    use HasFactory, \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return [];
    }

    protected $fillable = [
        'company',
        'location',
    ];

    protected $casts = [
        //
    ];

    protected $appends = ['image_url'];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    /**
     * Get the path to the experience image file, if it exists.
     */
    public function getImagePath(): ?string
    {
        $dir = storage_path('experiences/' . $this->id);

        if (!is_dir($dir)) {
            return null;
        }

        $files = glob($dir . '/logo.*');

        return !empty($files) ? $files[0] : null;
    }

    /**
     * Accessor: image_url
     */
    public function getImageUrlAttribute(): ?string
    {
        $path = $this->getImagePath();

        if (!$path) {
            return null;
        }

        return route('experiences.image', $this->id) . '?v=' . filemtime($path);
    }

    public function roles()
    {
        return $this->hasMany(ExperienceRole::class)->orderBy('start_date', 'desc');
    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }
}
