<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['expires_at' => 'datetime', 'paid_at' => 'datetime', 'amount' => 'integer'];

    protected $hidden = ['idempotency_key', 'request_hash'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function depositRequest()
    {
        return $this->belongsTo(DepositRequest::class);
    }
}
