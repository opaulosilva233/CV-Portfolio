<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    use HasFactory, \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return ['description'];
    }

    protected $fillable = [
        'project_id',
        'filename',
        'image_data',
        'mime_type',
        'description',
        'is_principal',
        'sort_order',
    ];

    protected $casts = [
        'is_principal' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $hidden = [
        'image_data',
    ];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute(): string
    {
        return route('projects.image', [
            'project' => $this->project_id,
            'filename' => $this->id,
        ]) . '?v=' . ($this->updated_at ? $this->updated_at->timestamp : time());
    }

    protected static function booted(): void
    {
        static::addGlobalScope('withoutBlob', function ($builder) {
            $builder->select([
                'id',
                'project_id',
                'filename',
                'mime_type',
                'description',
                'is_principal',
                'sort_order',
                'created_at',
                'updated_at',
            ]);
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
