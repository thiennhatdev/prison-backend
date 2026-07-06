<?php

namespace App\Enums;

enum PtEnum: string
{
    case QUAN_LY_PHAM_NHAN = 'QUAN_LY_PHAM_NHAN';
    case TAM_GIAM = 'TAM_GIAM';
    case HAI_AN = 'HAI_AN';
    case AN_DUONG = 'AN_DUONG';
    case VINH_BAO = 'VINH_BAO';
    case THUY_NGUYEN = 'THUY_NGUYEN';
    case HUNG_DAO = 'HUNG_DAO';

    public function label(): string
    {
        return match ($this) {
            self::QUAN_LY_PHAM_NHAN => 'Phân trại quản lý phạm nhân',
            self::TAM_GIAM => 'Phân trại tạm giam',
            self::HAI_AN => 'Phân trại Hải An',
            self::AN_DUONG => 'Phân trại An Dương',
            self::VINH_BAO => 'Phân trại Vĩnh Bảo',
            self::THUY_NGUYEN => 'Phân trại Thủy Nguyên',
            self::HUNG_DAO => 'Phân trại Hưng Đạo',
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
