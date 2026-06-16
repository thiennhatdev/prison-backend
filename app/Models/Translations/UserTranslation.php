<?php

namespace App\Models\Translations;

use A17\Twill\Models\Model;
use App\Models\User;

class UserTranslation extends Model
{
    protected $baseModuleModel = User::class;
}
