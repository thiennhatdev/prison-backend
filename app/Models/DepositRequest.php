<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $guarded = ['id'];

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
