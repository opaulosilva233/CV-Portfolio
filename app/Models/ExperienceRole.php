<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienceRole extends Model
{
    use \App\Traits\HasTranslations;

    public function getTranslatableFields()
    {
        return ['role', 'description'];
    }

    protected $fillable = [
        'experience_id',
        'role',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }
}
