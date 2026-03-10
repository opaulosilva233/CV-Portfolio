<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    /** @use HasFactory<\Database\Factories\ExperienceFactory> */
    use HasFactory, \App\Traits\HasTranslations;

    public function getTranslatableFields()
    {
        return ['company', 'location'];
    }

    protected $fillable = [
        'company',
        'location',
    ];

    protected $casts = [
        //
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
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
