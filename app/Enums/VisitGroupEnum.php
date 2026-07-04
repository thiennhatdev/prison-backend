<?php

namespace App\Enums;

enum VisitGroupEnum: string
{
    case INDIVIDUAL = 'INDIVIDUAL';
    case ORGANIZATION = 'ORGANIZATION';

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Thân thích',
            self::ORGANIZATION => 'Đại diện cơ quan, tổ chức, cá nhân khác',
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