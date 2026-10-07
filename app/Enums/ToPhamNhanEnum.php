<?php

namespace App\Enums;

enum ToPhamNhanEnum: string
{
    case TO_1 = 'TO_1';
    case TO_2 = 'TO_2';
    case TO_3 = 'TO_3';
    case TO_4 = 'TO_4';
    case TO_5 = 'TO_5';
    case TO_6 = 'TO_6';
    case TO_7 = 'TO_7';
    case TO_NU = 'TO_NU';
    case UNKNOWN = 'UNKNOWN';

    public function label(): string
    {
        return match ($this) {
            self::TO_1 => 'Tổ 1',
            self::TO_2 => 'Tổ 2',
            self::TO_3 => 'Tổ 3',
            self::TO_4 => 'Tổ 4',
            self::TO_5 => 'Tổ 5',
            self::TO_6 => 'Tổ 6',
            self::TO_7 => 'Tổ 7',
            self::TO_NU => 'Tổ nữ phân trại',
            self::UNKNOWN => 'Không rõ',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}