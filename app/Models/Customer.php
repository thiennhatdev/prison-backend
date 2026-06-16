<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'zalo_id',
        'phone',
        'name',
        'avatar',
        'is_active',
        'last_login_at',
        'role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

     public function visitationSchedules()
    {
        return $this->hasMany(
            VisitationSchedule::class,
            'customer_id'
        );
    }
}
