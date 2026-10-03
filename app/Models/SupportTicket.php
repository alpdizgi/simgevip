<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['customer_id', 'subject', 'category', 'status', 'priority', 'last_reply_at', 'customer_read_at', 'admin_read_at'];

    protected $casts = ['last_reply_at' => 'datetime', 'customer_read_at' => 'datetime', 'admin_read_at' => 'datetime'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function messages() { return $this->hasMany(SupportMessage::class); }
}
