<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}
