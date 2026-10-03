<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'color',
        'size',
        'customer_name',
        'phone',
        'email',
        'reservation_date',
        'guests_count',
        'status',
        'is_admin_hold',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'is_admin_hold' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getParsedNotesAttribute(): array
    {
        $raw = (string) ($this->attributes['notes'] ?? '');
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $color = $this->attributes['color'] ?? null;
        $size = $this->attributes['size'] ?? null;
        $sku = null;
        $customerNotes = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^Renk:\s*(.+)$/iu', $line, $m)) {
                if (empty($color)) $color = trim($m[1]);
            } elseif (preg_match('/^Beden:\s*(.+)$/iu', $line, $m)) {
                if (empty($size)) $size = trim($m[1]);
            } elseif (preg_match('/^SKU:\s*(.+)$/iu', $line, $m)) {
                $sku = trim($m[1]);
            } elseif (preg_match('/^Not:\s*(.+)$/iu', $line, $m)) {
                $customerNotes[] = trim($m[1]);
            } elseif (preg_match('/^(Mağazada ayırma talebi|Ürün:|Link:)/iu', $line)) {
                continue;
            } else {
                $customerNotes[] = $line;
            }
        }

        return [
            'color' => $color,
            'size' => $size,
            'sku' => $sku,
            'clean_notes' => implode("\n", $customerNotes),
        ];
    }

    public function getCleanNotesAttribute(): string
    {
        return $this->parsed_notes['clean_notes'] ?? '';
    }

    public function getSelectedColorAttribute(): ?string
    {
        return $this->attributes['color'] ?? ($this->parsed_notes['color'] ?? null);
    }

    public function getSelectedSizeAttribute(): ?string
    {
        return $this->attributes['size'] ?? ($this->parsed_notes['size'] ?? null);
    }
}
