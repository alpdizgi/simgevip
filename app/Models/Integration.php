<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'api_key', 'api_secret', 'config', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'config' => 'encrypted',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'config',
    ];
}
