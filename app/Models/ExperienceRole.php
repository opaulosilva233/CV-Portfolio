<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienceRole extends Model
{
    use \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return ['role', 'description'];
    }

    protected $fillable = [
        'experience_id',
        'role',
        'employment_type',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'education_id',
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

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }

    public function education()
    {
        return $this->belongsTo(Education::class);
    }
}
