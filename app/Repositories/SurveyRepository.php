<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleFiles;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Survey;

class SurveyRepository extends ModuleRepository
{
    use HandleBlocks, HandleSlugs, HandleMedias, HandleFiles, HandleRevisions;

    public function __construct(Survey $model)
    {
        $this->model = $model;
    }

    public function detail($id)
    {
        $detail = Survey::where('id', $id)
            ->published() 
            ->first();
        return [
                    'id' => $detail->id,
                    'title' => $detail->title,
                    'description' => $detail->description,
                    'code' => $detail->code,
                    'point' => $detail->point,
                    'created_at' => $detail->created_at,
            ];
    }

    public function list($limit = 10)
    {
        return $this->model
            ->published()
            ->latest()
            ->paginate($limit);
    }
}
