<?php

namespace App\Enums;

enum PtEnum: string
{
    case PT1 = 'PT1';
    case PT2 = 'PT2';
    case PT3 = 'PT3';
    case PT4 = 'PT4';
    case PT5 = 'PT5';
    case PT6 = 'PT6';
    case PT7 = 'PT7';
    case PT8 = 'PT8';
    case PT_FEMALE = 'PT_FEMALE';

    public function label(): string
    {
        return match ($this) {
            self::PT1 => 'Phân trại 1',
            self::PT2 => 'Phân trại 2',
            self::PT3 => 'Phân trại 3',
            self::PT4 => 'Phân trại 4',
            self::PT5 => 'Phân trại 5',
            self::PT6 => 'Phân trại 6',
            self::PT7 => 'Phân trại 7',
            self::PT8 => 'Phân trại 8',
            self::PT_FEMALE => 'Phân trại Nữ',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }

    public static function labelOf(?string $value): string
    {
        return self::tryFrom($value)?->label() ?? '';
    }
}
