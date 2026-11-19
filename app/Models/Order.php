<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['sender_details' => 'array', 'recipient_details' => 'array'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function prisoner()
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
