<?php

namespace App\Containers\AppSection\Profile\Enums;

use Filament\Support\Contracts\HasLabel;

enum EducationType: string implements HasLabel
{
    case UNIVERSITY = 'university';
    case COURSE = 'course';
    case CERTIFICATE = 'certificate';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::UNIVERSITY => 'Університет',
            self::COURSE => 'Курс',
            self::CERTIFICATE => 'Сертифікат',
        };
    }
}
