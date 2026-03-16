<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, \App\Traits\HasTranslations;

    public function getTranslatableFields()
    {
        return ['title', 'description'];
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_url',
        'project_url',
        'github_url',
        'tech_stack',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'is_featured' => 'boolean',
    ];

    protected $appends = ['main_image_url', 'gallery'];

    /**
     * Get the gallery of images for the project.
     * Returns an array of objects: { url: string, description: string, is_principal: boolean, filename: string }
     */
    public function getGalleryAttribute(): array
    {
        $dir = storage_path('projects/' . $this->id);
        if (!is_dir($dir)) {
            return [];
        }

        $allFiles = glob($dir . '/*.*');
        $metadata = [];
        if (file_exists($dir . '/metadata.json')) {
            $json = json_decode(file_get_contents($dir . '/metadata.json'), true);
            $locale = \Illuminate\Support\Facades\App::getLocale();
            $metadata = $json[$locale] ?? $json['en'] ?? $json['pt'] ?? [];
        }

        $gallery = [];
        foreach ($allFiles as $filePath) {
            $filename = basename($filePath);
            if ($filename === 'metadata.json') continue;

            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            $isPrincipal = $nameWithoutExt === 'principal';
            
            $gallery[] = [
                'filename' => $filename,
                'url' => route('projects.image', ['project' => $this->id, 'filename' => $filename]),
                'description' => $metadata[$nameWithoutExt] ?? '',
                'is_principal' => $isPrincipal,
                'sort_order' => $isPrincipal ? -1 : (is_numeric($nameWithoutExt) ? (int)$nameWithoutExt : 999),
            ];
        }

        usort($gallery, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        return $gallery;
    }

    /**
     * Accessor: main_image_url
     */
    public function getMainImageUrlAttribute(): ?string
    {
        $dir = storage_path('projects/' . $this->id);
        $files = glob($dir . '/principal.*');

        if (empty($files)) {
            return null;
        }

        $filename = basename($files[0]);
        return route('projects.image', ['project' => $this->id, 'filename' => $filename]) . '?v=' . filemtime($files[0]);
    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }
}
