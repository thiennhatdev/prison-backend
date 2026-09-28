<?php

namespace App\Exports;

use App\Models\Prisoner;
use App\Enums\RelationshipEnum;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PrisonerExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        $data = collect();

        Prisoner::query()
            ->orderBy('prisoner_code')
            ->get()
            ->each(function ($prisoner) use ($data) {

                $phones = is_array($prisoner->phones)
                    ? $prisoner->phones
                    : [];

                /**
                 * Không có thân nhân
                 */
                if (empty($phones)) {
                    $data->push([
                        $prisoner->prisoner_code,
                        $prisoner->username,
                        '',
                        '',
                        '',
                    ]);

                    return;
                }

                /**
                 * Có thân nhân
                 */
                foreach ($phones as $index => $phone) {

                    $data->push([
                        // Chỉ ghi thông tin phạm nhân ở dòng đầu tiên
                        $index === 0 ? $prisoner->prisoner_code : '',
                        $index === 0 ? $prisoner->username : '',

                        $phone['name'] ?? '',

                        $this->relationshipLabel(
                            $phone['relationship'] ?? ''
                        ),

                        $phone['phone'] ?? '',
                    ]);
                }
            });

        return $data;
    }

    public function headings(): array
    {
        return [
            'Số giam',
            'Tên phạm nhân',
            'Tên thân nhân',
            'Mối quan hệ',
            'Số điện thoại',
        ];
    }

    private function relationshipLabel(?string $value): string
    {
        if (!$value) {
            return '';
        }

        foreach (RelationshipEnum::cases() as $case) {
            if ($case->value === $value) {
                return $case->label();
            }
        }

        return $value;
    }
}