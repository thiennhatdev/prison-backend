<?php

namespace App\Imports;

use App\Models\Prisoner;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Enums\RelationshipEnum;

class PrisonerImport implements ToCollection, WithHeadingRow
{
    
    public function collection(Collection $rows)
    {
        // dd($rows->first());
        $currentPrisoner = null;
        $phones = [];

        foreach ($rows as $row) {

            $prisonerCode = trim($row['so_giam'] ?? '');
            $username = trim($row['ten_pham_nhan'] ?? '');

            /**
             * Gặp phạm nhân mới
             */
            if ($prisonerCode !== '') {

                // Lưu phạm nhân trước đó
                if ($currentPrisoner) {

                    $prisoner = Prisoner::updateOrCreate(
                        [
                            'prisoner_code' => $currentPrisoner['prisoner_code']
                        ],
                        [
                            'title' => $currentPrisoner['username'],
                            'username' => $currentPrisoner['username'],
                            'phones' => $phones,
                        ]
                    );

                }

                $currentPrisoner = [
                    'prisoner_code' => $prisonerCode,
                    'username' => $username,
                ];

                $phones = [];
            }

            /**
             * Thêm thân nhân
             */
            if (!empty($row['ten_than_nhan'])) {

                $phones[] = [
                    'name' => trim($row['ten_than_nhan']),
                    'relationship' => $this->mapRelationship(
                        $row['moi_quan_he'] ?? ''
                    ),
                    'phone' => trim((string) ($row['so_dien_thoai'] ?? '')),
                ];
            }
        }

        /**
         * Lưu phạm nhân cuối cùng
         */
        if ($currentPrisoner) {

            Prisoner::updateOrCreate(
                [
                    'prisoner_code' => $currentPrisoner['prisoner_code']
                ],
                [
                    'username' => $currentPrisoner['username'],
                    'phones' => $phones,
                ]
            );
        }
    }

    private function mapRelationship(?string $value): string
{
    $value = mb_strtolower(trim($value ?? ''));

    foreach (RelationshipEnum::cases() as $case) {
        if (mb_strtolower($case->label()) === $value) {
            return $case->value;
        }
    }

    throw new \InvalidArgumentException("Mối quan hệ không hợp lệ: {$value}");
}

}