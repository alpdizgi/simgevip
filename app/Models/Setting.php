<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'meta_title',
        'meta_description',
        'logo_path',
        'og_image_path',
        'contact_info',
        'social_media',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'social_media' => 'array',
    ];
}
