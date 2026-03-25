<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    /** @use HasFactory<\Database\Factories\SkillFactory> */
    use HasFactory, \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return ['category'];
    }

    protected $fillable = [
        'name',
        'category',
        'proficiency',
        'icon',
        'sort_order',
    ];

    public function projects()
    {
        return $this->morphedByMany(Project::class, 'skillable');
    }

    public function educations()
    {
        return $this->morphedByMany(Education::class, 'skillable');
    }

    public function experiences()
    {
        return $this->morphedByMany(Experience::class, 'skillable');
    }
}
