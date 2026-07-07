<?php

namespace App\Enums;

enum VisitationScheduleStatusEnum: string
{
    case NOT_YET = 'NOT_YET';
    case DONE = 'DONE';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::NOT_YET => 'Sắp tới',
            self::DONE => 'Đã thăm',
            self::EXPIRED => 'Hết hạn',
        };
    }

    public static function labelOf(?string $value): string
    {
        return match ($value) {
            self::NOT_YET->value => self::NOT_YET->label(),
            self::DONE->value => self::DONE->label(),
            self::EXPIRED->value => self::EXPIRED->label(),
            default => '',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}