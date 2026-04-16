<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'section',
        'path',
        'session_id',
        'duration_seconds',
    ];
}
