<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    /** @use HasFactory<\Database\Factories\EducationFactory> */
    use HasFactory, \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return ['degree', 'description'];
    }

    protected $fillable = [
        'institution',
        'degree',
        'start_date',
        'end_date',
        'is_current',
        'type',
        'url',
        'description',
        'image_data',
        'image_mime_type',
    ];

    protected $hidden = [
        'image_data',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    protected $appends = ['image_url'];

    /**
     * Accessor: image_url
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_data)) {
            return null;
        }

        return route('educations.image', $this->id) . '?v=' . ($this->updated_at ? $this->updated_at->timestamp : time());
    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }

    public function experienceRoles()
    {
        return $this->hasMany(ExperienceRole::class);
    }
}
