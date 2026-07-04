<?php

namespace App\Enums;

enum RelationshipEnum: string
{
    case VO = 'VO';
    case CHONG = 'CHONG';
    case BO_DE = 'BO_DE';
    case ME_DE = 'ME_DE';
    case BO_CHONG = 'BO_CHONG';
    case ME_CHONG = 'ME_CHONG';
    case BO_VO = 'BO_VO';
    case ME_VO = 'ME_VO';
    case BO_NUOI = 'BO_NUOI';
    case ME_NUOI = 'ME_NUOI';
    case CON_DE = 'CON_DE';
    case CON_NUOI = 'CON_NUOI';
    case CON_DAU = 'CON_DAU';
    case CON_RE = 'CON_RE';
    case ONG_NOI = 'ONG_NOI';
    case BA_NOI = 'BA_NOI';
    case ONG_NGOAI = 'ONG_NGOAI';
    case BA_NGOAI = 'BA_NGOAI';
    case ANH_RUOT = 'ANH_RUOT';
    case CHI_RUOT = 'CHI_RUOT';
    case EM_RUOT = 'EM_RUOT';
    case CU_NOI = 'CU_NOI';
    case CU_NGOAI = 'CU_NGOAI';
    case BAC_RUOT = 'BAC_RUOT';
    case CHU_RUOT = 'CHU_RUOT';
    case CAU_RUOT = 'CAU_RUOT';
    case DI_RUOT = 'DI_RUOT';
    case CO_RUOT = 'CO_RUOT';
    case CHAU_RUOT = 'CHAU_RUOT';
    case CHAT_RUOT = 'CHAT_RUOT';

    public function label(): string
    {
        return match ($this) {
            self::VO => 'Vợ',
            self::CHONG => 'Chồng',
            self::BO_DE => 'Bố đẻ',
            self::ME_DE => 'Mẹ đẻ',
            self::BO_CHONG => 'Bố chồng',
            self::ME_CHONG => 'Mẹ chồng',
            self::BO_VO => 'Bố vợ',
            self::ME_VO => 'Mẹ vợ',
            self::BO_NUOI => 'Bố nuôi',
            self::ME_NUOI => 'Mẹ nuôi',
            self::CON_DE => 'Con đẻ',
            self::CON_NUOI => 'Con nuôi',
            self::CON_DAU => 'Con dâu',
            self::CON_RE => 'Con rễ',
            self::ONG_NOI => 'Ông nội',
            self::BA_NOI => 'Bà nội',
            self::ONG_NGOAI => 'Ông ngoại',
            self::BA_NGOAI => 'Bà ngoại',
            self::ANH_RUOT => 'Anh ruột',
            self::CHI_RUOT => 'Chị ruột',
            self::EM_RUOT => 'Em ruột',
            self::CU_NOI => 'Cụ nội',
            self::CU_NGOAI => 'Cụ ngoại',
            self::BAC_RUOT => 'Bác ruột',
            self::CHU_RUOT => 'Chú ruột',
            self::CAU_RUOT => 'Cậu ruột',
            self::DI_RUOT => 'Dì ruột',
            self::CO_RUOT => 'Cô ruột',
            self::CHAU_RUOT => 'Cháu ruột',
            self::CHAT_RUOT => 'Chắt ruột'
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

    public static function twillOptions(): array
    {
        return array_map(
            fn ($case) => \A17\Twill\Services\Forms\Option::make(
                $case->value,
                $case->label()
            ),
            self::cases()
        );
    }
}