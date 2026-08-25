<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    /** @use HasFactory<\Database\Factories\PageSectionFactory> */
    use HasFactory, \App\Traits\HasTranslations, \App\Traits\ClearsPortfolioCache;

    public function getTranslatableFields()
    {
        return ['title'];
    }

    protected $fillable = [
        'name',
        'title',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
