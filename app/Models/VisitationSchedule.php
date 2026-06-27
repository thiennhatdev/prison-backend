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
use Carbon\Carbon;

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

    public function getVisitTimeLabelAttribute(): string
    {
        $start = Carbon::createFromFormat('H:i:s', $this->visitTime);
        
        return $start->format('H:i') . ' - ' .
            $start->copy()->addHour()->format('H:i');
    }

    public function getStatusLabelAttribute(): string
    {
        // Nếu có lý do từ chối thì ưu tiên hiển thị "Từ chối"
        if (!empty($this->refuse)) {
            return 'Từ chối';
        }

        return match ($this->status) {
            'DONE' => 'Đã thăm',
            'NOT_YET' => 'Sắp tới',
            default => 'Không xác định',
        };
    }
    
}
