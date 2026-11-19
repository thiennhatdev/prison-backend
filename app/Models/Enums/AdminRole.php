<?php

namespace App\Models\Enums;

use A17\Twill\Models\Enums\UserRole;
use A17\Twill\Models\Role;
use A17\Twill\Models\User;

class AdminRole extends UserRole
{
    public const NEW = 'NEW';

    public static function isNewUser(User $user): bool
    {
        $role = $user->role;

        return strtoupper(trim((string) ($role instanceof Role ? $role->name : $role))) === self::NEW;
    }
}
