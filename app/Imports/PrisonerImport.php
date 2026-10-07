<?php

namespace App\Imports;

use App\Models\Prisoner;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Enums\RelationshipEnum;
use App\Enums\ToPhamNhanEnum;

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
                            'prisoner_sex' => $currentPrisoner['prisoner_sex'],
                            'prisoner_birthday' => $currentPrisoner['prisoner_birthday'],
                            'prisoner_address' => $currentPrisoner['prisoner_address'],
                            'phones' => $phones,
                            'to' => $currentPrisoner['to'],
                        ]
                    );

                }

                $currentPrisoner = [
                    'prisoner_code' => $prisonerCode,
                    'username' => $username,
                    'prisoner_sex' => $this->mapSex(
                        $row['gioi_tinh'] ?? ''
                    ),
                    'prisoner_birthday' => trim(
                        (string) ($row['nam_sinh'] ?? '')
                    ),
                    'prisoner_address' => trim(
                        (string) ($row['dia_chi'] ?? '')
                    ),
                    'to' => $this->mapToPhamNhan(
                        $row['to_pham_nhan'] ?? ''
                    ),
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
                    'title' => $currentPrisoner['username'],
                    'username' => $currentPrisoner['username'],
                    'prisoner_sex' => $currentPrisoner['prisoner_sex'],
                    'prisoner_birthday' => $currentPrisoner['prisoner_birthday'],
                    'prisoner_address' => $currentPrisoner['prisoner_address'],
                    'phones' => $phones,
                    'to' => $currentPrisoner['to'],
                ]
            );
        }
    }

    private function mapSex(?string $value): ?string
    {
        $value = mb_strtolower(trim($value ?? ''));

        return match ($value) {
            'nam' => 'MALE',
            'nữ', 'nu' => 'FEMALE',
            '' => null,
            default => throw new \InvalidArgumentException(
                "Giới tính không hợp lệ: {$value}"
            ),
        };
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

    private function mapToPhamNhan(?string $value): ?string
    {
        $value = mb_strtolower(trim($value ?? ''));

        foreach (ToPhamNhanEnum::cases() as $case) {
            if (mb_strtolower($case->label()) === $value) {
                return $case->value;
            }
        }

        if ($value === '') {
            return null;
        }

        throw new \InvalidArgumentException(
            "Tổ phạm nhân không hợp lệ: {$value}"
        );
    }

}