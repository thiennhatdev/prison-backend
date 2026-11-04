<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['accessed_at' => 'datetime'];
}
