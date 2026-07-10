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
use App\Enums\PtEnum;
use App\Enums\VisitGroupEnum;
use App\Enums\ChildVisitGroupEnum;
use App\Enums\VisitationScheduleStatusEnum;

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
        'visitEndTime',
        'count',
        'qr_token',
        'customer_id',
        'status',
        'pt',
        'visitGroup',
        'childVisitGroup',
        'identification',
        'prisoner_name',
        'prisoner_birthday',
        'prisoner_sex',
        'prisoner_address',
        'refuse',
        'reason'
    ];
    
    public $translatedAttributes = [
        'title',
        'description',
    ];
    
    public $slugAttributes = [
        'title',
    ];

    protected $casts = [
        'relatives' => 'array',
        'pt' => PtEnum::class,
        'visitGroup' => VisitGroupEnum::class,
        'childVisitGroup' => ChildVisitGroupEnum::class,
    ];

    public function getVisitTimeLabelAttribute(): string
    {
        $start = Carbon::createFromFormat('H:i:s', $this->visitTime);
        if (empty($this->visitEndTime)) {
            return $start->format('H:i');
        }

        $end = Carbon::createFromFormat('H:i:s', $this->visitEndTime);
        return $start->format('H:i') . ' - ' .
            $end->format('H:i');
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
            'EXPIRED' => 'Hết hạn',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!empty($this->refuse)) {
            return '<span style="padding:4px 10px;border-radius:999px;background:#FEE2E2;color:#B91C1C;font-weight:600; text-align: center;">Từ chối</span>';
        }

        return match ($this->status) {
            VisitationScheduleStatusEnum::NOT_YET->value =>
                '<span style="padding:4px 10px;border-radius:999px;background:#FEF3C7;color:#92400E;font-weight:600; text-align: center;">Sắp tới</span>',

            VisitationScheduleStatusEnum::DONE->value =>
                '<span style="padding:4px 10px;border-radius:999px;background:#DCFCE7;color:#166534;font-weight:600; text-align: center;">Đã thăm</span>',

            VisitationScheduleStatusEnum::EXPIRED->value =>
                '<span style="padding:4px 10px;border-radius:999px;background:#ccc;color:#666;font-weight:600; text-align: center;">Hết hạn</span>',

            default =>
                '<span style="padding:4px 10px;border-radius:999px;background:#E5E7EB;color:#374151;font-weight:600; text-align: center;">Không xác định</span>',
        };
    }

    public function getVisitWeekdayLabelAttribute(): string
    {
        $days = [
            'Chủ nhật',
            'Thứ Hai',
            'Thứ Ba',
            'Thứ Tư',
            'Thứ Năm',
            'Thứ Sáu',
            'Thứ Bảy',
        ];

        return $days[Carbon::parse($this->visitDate)->dayOfWeek];
    }

    public function getPtLabelAttribute(): string
    {
        return $this->pt?->label() ?? '';
    }

    public function getPrisonerSexLabelAttribute(): string
    {
        return [
            'MALE' => 'Nam',
            'FEMALE' => 'Nữ',
        ][$this->prisoner_sex] ?? '';
    }

    public function getVisitGroupLabelAttribute(): string
    {
        return $this->visitGroup?->label() ?? '';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
}
