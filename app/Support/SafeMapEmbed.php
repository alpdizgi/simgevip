<?php

namespace App\Support;

class SafeMapEmbed
{
    /**
     * Accept a Google Maps embed URL or an iframe snippet and return a safe embed URL, or null.
     */
    public static function url(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $input = trim($input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/src\s*=\s*([\'"])(.*?)\1/i', $input, $matches)) {
            $input = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
        }

        if (! filter_var($input, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($input);

        if (($parts['scheme'] ?? '') !== 'https') {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');

        $allowedHosts = [
            'www.google.com',
            'google.com',
            'maps.google.com',
            'www.google.com.tr',
            'maps.google.com.tr',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            return null;
        }

        $path = $parts['path'] ?? '';

        if (! str_starts_with($path, '/maps/embed')) {
            return null;
        }

        return $input;
    }
}
