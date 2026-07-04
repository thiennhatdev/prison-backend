<?php

namespace App\Enums;

enum ChildVisitGroupEnum: string
{
    case AGENCY = 'AGENCY';
    case ORGANIZATION = 'ORGANIZATION';
    case INDIVIDUAL = 'INDIVIDUAL';

    public function label(): string
    {
        return match ($this) {
            self::AGENCY => 'Cơ quan',
            self::ORGANIZATION => 'Tổ chức',
            self::INDIVIDUAL => 'Cá nhân khác',
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