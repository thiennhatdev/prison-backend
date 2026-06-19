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

class VisitationSchedule extends Model implements Sortable
{
    use HasBlocks, HasTranslation, HasSlug, HasMedias, HasFiles, HasRevisions, HasPosition, HasFactory;

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
        'relatives',
        'prisoner_id',
        'visitDate',
        'visitTime',
        'count',
        'qr_token',
        'customer_id',
        'status',
        'prisoner_name',
        'prisoner_birthday',
        'prisoner_address',
        'refuse',
    ];
    
    public $translatedAttributes = [
        'title',
        'description',
    ];
    
    public $slugAttributes = [
        'title',
    ];

    protected $casts = [
        'relatives' => 'array'
    ];

    public function customer()
        {
            return $this->belongsTo(Customer::class);
        }

    public function getStatusLabelAttribute()
    {
        return [
            'NOT_YET' => 'Sắp tới',
            'DONE' => 'Đã thăm',
        ][$this->status] ?? '';
    }
    
}
