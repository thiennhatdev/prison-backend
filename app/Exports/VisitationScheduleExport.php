<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\VisitationSchedule;
use App\Enums\PtEnum;
use App\Enums\VisitGroupEnum;
use App\Enums\ChildVisitGroupEnum;

class VisitationScheduleExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected $items
    ) {}

    /**
    * @return \Illuminate\Support\Collection
    */
     public function collection()
    {
        return $this->items->map(function ($item, $index) {
                return [
                    'stt' => $index + 1,
                    'visitDate' =>\Carbon\Carbon::parse($item->visitDate)->format('d/m/Y'),
                    'visitTime' => \Carbon\Carbon::parse($item->visitTime)->format('H:i'),
                    'status' => [
                        'NOT_YET' => 'Sắp tới',
                        'DONE' => 'Đã thăm',
                    ][$item->status] ?? $item->status,
                    'prisoner_name' => $item->prisoner_name,
                    'prisoner_sex' => [
                        'MALE' => 'Nam',
                        'FEMALE' => 'Nữ',
                    ][$item->prisoner_sex] ?? $item->prisoner_sex,
                    'pt' => $item->pt?->label(),
                    'customer' => $item->customer?->name,
                    'username' => collect($item->relatives)
                        ->pluck('username')
                        ->implode(', '),
                    'identification' => $item->identification,
                    'count' => $item->count,
                    'cccd' => collect($item->relatives)
                        ->pluck('cccd')
                        ->implode(', '),
                    'phone' => collect($item->relatives)
                        ->pluck('phone')
                        ->implode(', '),
                    'address' => collect($item->relatives)
                        ->pluck('address')
                        ->implode(', '),
                    ];
            });
    }

    public function headings(): array
    {
        return [
            'STT',
            'Ngày thăm',
            'Giờ thăm',
            'Trạng thái',
            'Phạm nhân',
            'Giới tính',
            'Phân trại',
            'Thân nhân đăng ký',
            'Người thân thích',
            'Đại diện cơ quan, tổ chức, cá nhân khác',
            'Số lượng',
            'Số CCCD',
            'Số điện thoại',
            'Địa chỉ',
            
        ];
    }
}
