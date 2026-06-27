<?php

namespace App\Enums;

enum RelationshipEnum: string
{
    case FATHER = 'FATHER';
    case MOTHER = 'MOTHER';
    case WIFE = 'WIFE';
    case HUSBAND = 'HUSBAND';
    case CHILD = 'CHILD';
    case NURTURER = 'NURTURER';

    public function label(): string
    {
        return match ($this) {
            self::FATHER => 'Bố',
            self::MOTHER => 'Mẹ',
            self::WIFE => 'Vợ',
            self::HUSBAND => 'Chồng',
            self::CHILD => 'Con',
            self::NURTURER => 'Người nuôi dưỡng',
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