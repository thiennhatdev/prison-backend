<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleFiles;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\PrisonRule;

class PrisonRuleRepository extends ModuleRepository
{
    use HandleBlocks, HandleSlugs, HandleMedias, HandleFiles, HandleRevisions;

    public function __construct(PrisonRule $model)
    {
        $this->model = $model;
    }

    public function detail($id)
    {
        $detail = PrisonRule::where('id', $id)
            ->published() 
            ->with('blocks')
            ->first();
        return [
                    'id' => $detail->id,
                    'title' => $detail->title,
                    'description' => $detail->description,
                    'thumbnail' => $detail->image('thumbnail'),
                    'blocks' => $detail->blocks,
                    'created_at' => $detail->created_at,
            ];
    }

      public function list($limit = 10)
    {
        return $this->model
            ->published()
            ->with('blocks')
            ->latest()
            ->paginate($limit)
            ->through(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'thumbnail' => $post->image('thumbnail'),
                    'blocks' => $post->blocks,
                    'created_at' => $post->created_at,
                ];
            });
    }
}
