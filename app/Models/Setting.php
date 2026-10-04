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
        'maintenance_mode',
        'maintenance_message',
        'mail_settings',
        'whatsapp_number',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'social_media' => 'array',
        'mail_settings' => 'array',
        'maintenance_mode' => 'boolean',
    ];
}
