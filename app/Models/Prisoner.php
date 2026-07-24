<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prisoner extends Model implements Sortable
{
    use HasBlocks, HasTranslation, HasSlug, HasMedias, HasFiles, HasRevisions, HasPosition, HasFactory;

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
        'prisoner_code',
        'username',
        'is_allow_visit',
        'prisoner_birthday',
        'prisoner_sex',
        'prisoner_address',
        'phones',
    ];
    
    public $translatedAttributes = [
        'title',
        'description',
    ];

     protected $casts = [
        'phones' => 'array',
    ];
    
    public $slugAttributes = [
        'title',
    ];

     public function visitationSchedules(): HasMany
        {
        return $this->hasMany(VisitationSchedule::class, 'prisoner_id');
        }
    
}
