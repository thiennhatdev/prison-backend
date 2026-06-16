<?php

namespace App\Models\Translations;

use A17\Twill\Models\Model;
use App\Models\Prisoner;

class PrisonerTranslation extends Model
{
    protected $baseModuleModel = Prisoner::class;
}
