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
        'announcements',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'social_media' => 'array',
        'mail_settings' => 'array',
        'announcements' => 'array',
        'maintenance_mode' => 'boolean',
    ];

    public function getWhatsappUrlAttribute(): string
    {
        $raw = $this->contact_info[0]['whatsapp'] ?? $this->whatsapp_number ?? null;

        if (! $raw && is_array($this->social_media)) {
            foreach ($this->social_media as $sm) {
                if (strtolower($sm['platform'] ?? '') === 'whatsapp' && ! empty($sm['url'])) {
                    $raw = $sm['url'];
                    break;
                }
            }
        }

        if (! $raw) {
            return 'https://wa.me/905550000000';
        }

        if (str_starts_with($raw, 'http')) {
            return $raw;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($digits, '0')) {
            $digits = '90' . substr($digits, 1);
        } elseif (! str_starts_with($digits, '90') && strlen($digits) === 10) {
            $digits = '90' . $digits;
        }

        return 'https://wa.me/' . ($digits ?: '905550000000');
    }

    public function getWhatsappDigitsAttribute(): string
    {
        $url = $this->whatsapp_url;
        return preg_replace('/\D+/', '', parse_url($url, PHP_URL_PATH) ?? '') ?: '905550000000';
    }
}
