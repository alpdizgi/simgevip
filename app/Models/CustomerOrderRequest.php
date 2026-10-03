<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderRequest extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Beklemede',
        'confirmed' => 'Onaylandı',
        'shipped' => 'Gönderildi',
        'cancel_requested' => 'İptal İsteği',
        'cancelled' => 'İptal Edildi',
    ];

    protected $fillable = ['customer_id', 'source', 'status', 'cancel_reason', 'items', 'total'];

    protected $casts = ['items' => 'array', 'total' => 'decimal:2'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
