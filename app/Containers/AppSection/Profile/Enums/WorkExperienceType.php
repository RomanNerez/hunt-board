<?php

namespace App\Containers\AppSection\Profile\Enums;

use Filament\Support\Contracts\HasLabel;

enum WorkExperienceType: string implements HasLabel
{
    case COMPANY = 'company';
    case FREELANCE = 'freelance';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::COMPANY => 'Компанія',
            self::FREELANCE => 'Фриланс-проєкт',
        };
    }
}
