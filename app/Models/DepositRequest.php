<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['sender_details' => 'array', 'recipient_details' => 'array'];

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
