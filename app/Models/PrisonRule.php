<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use A17\Twill\Models\Model;

class PrisonRule extends Model implements Sortable
{
    use HasBlocks, HasSlug, HasMedias, HasFiles, HasRevisions, HasPosition, HasFactory;

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
    ];
    
    public $slugAttributes = [
        'title',
    ];

    public $mediasParams = [
        'thumbnail' => [
            'default' => [
                [
                    'name' => 'default',
                    'ratio' => 0
                ],
            ],
            'mobile' => [
                [
                    'name' => 'mobile',
                    'ratio' => 0,
                ],
            ],
            'flexible' => [
                [
                    'name' => 'free',
                    'ratio' => 0,
                ],
                [
                    'name' => 'landscape',
                    'ratio' => 16 / 9,
                ],
                [
                    'name' => 'portrait',
                    'ratio' => 3 / 5,
                ],
            ],
        ]
        ];
    
    
}
