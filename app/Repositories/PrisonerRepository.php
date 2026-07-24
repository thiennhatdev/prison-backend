<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleFiles;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleJsonRepeaters;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Prisoner;

class PrisonerRepository extends ModuleRepository
{
    use HandleBlocks, HandleTranslations, HandleSlugs, HandleMedias, HandleFiles, HandleRevisions, HandleJsonRepeaters;

    public function __construct(Prisoner $model)
    {
        $this->model = $model;
    }

    protected $jsonRepeaters = [
        'phones',
    ];

    public function prepareFieldsBeforeCreate(array $fields): array
    {
        if (empty($fields['code'])) {
            $lastRecord = Prisoner::orderByDesc('id')->first();

            $nextNumber = 1;

            if ($lastRecord && $lastRecord->code) {
                $nextNumber = (int) preg_replace('/\D/', '', $lastRecord->code) + 1;
            }

            $fields['code'] = 'PN' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return $fields;
    }
}
