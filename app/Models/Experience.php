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
        'image_data',
        'image_mime_type',
    ];

    protected $hidden = [
        'image_data',
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
     * Accessor: image_url
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_mime_type) && empty($this->image_data)) {
            return null;
        }

        return route('experiences.image', $this->id) . '?v=' . ($this->updated_at ? $this->updated_at->timestamp : time());
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
