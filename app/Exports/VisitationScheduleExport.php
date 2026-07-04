<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\VisitationSchedule;

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
        // return VisitationSchedule::query()
        //     ->with(['customer'])
        //     ->get()
        //     ->map(function ($item, $index) {
        return $this->items->map(function ($item, $index) {
                return [
                    'stt' => $index + 1,
                    'customer' => $item->customer?->name,
                    'prisoner_name' => $item->prisoner_name,
                    'count' => $item->count,
                    'visitDate' =>\Carbon\Carbon::parse($item->visitDate)->format('d/m/Y'),
                    'visitTime' => \Carbon\Carbon::parse($item->visitTime)->format('H:i'),
                    'status' => [
                        'NOT_YET' => 'Sắp tới',
                        'DONE' => 'Đã thăm',
                    ][$item->status] ?? $item->status,
                    ];
            });
    }

    public function headings(): array
    {
        return [
            'STT',
            'Thân nhân đăng ký',
            'Phạm nhân',
            'Số lượng',
            'Ngày thăm',
            'Giờ thăm',
            'Trạng thái',
        ];
    }
}
