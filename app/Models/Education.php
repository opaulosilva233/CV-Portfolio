<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    /** @use HasFactory<\Database\Factories\EducationFactory> */
    use HasFactory, \App\Traits\HasTranslations;

    public function getTranslatableFields()
    {
        return ['institution', 'degree', 'description'];
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
     * Get the path to the education image file, if it exists.
     */
    public function getImagePath(): ?string
    {
        $dir = storage_path('educations/' . $this->id);

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

        return route('educations.image', $this->id) . '?v=' . filemtime($path);
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
