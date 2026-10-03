<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = ['author_type', 'author_id', 'body'];

    public function ticket() { return $this->belongsTo(SupportTicket::class, 'support_ticket_id'); }
}
