<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return ['description'];
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_url',
        'project_url',
        'github_url',
        'is_featured',
        'in_progress',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'in_progress' => 'boolean',
        'completed_at' => 'date',
    ];

    protected $appends = ['main_image_url', 'gallery'];

    public function images()
    {
        return $this->hasMany(ProjectImage::class)
            ->select(['id', 'project_id', 'filename', 'mime_type', 'description', 'is_principal', 'sort_order', 'created_at', 'updated_at'])
            ->orderBy('is_principal', 'desc')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Get the gallery of images for the project from the database.
     * Returns an array of objects: { id: int, url: string, description: string, is_principal: boolean, filename: string, sort_order: int }
     */
    public function getGalleryAttribute(): array
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->with('translations')->get();

        return $images->map(function ($img) {
            return [
                'id' => $img->id,
                'filename' => $img->filename ?: ($img->is_principal ? 'principal.png' : $img->id . '.png'),
                'url' => $img->url,
                'description' => $img->translated('description') ?: ($img->description ?: ''),
                'is_principal' => (bool)$img->is_principal,
                'sort_order' => (int)$img->sort_order,
            ];
        })->sortBy('sort_order')->values()->toArray();
    }

    /**
     * Accessor: main_image_url
     */
    public function getMainImageUrlAttribute(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        $principal = $images->firstWhere('is_principal', true) ?? $images->first();

        return $principal ? $principal->url : null;
    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }
}
