<?php

namespace App\Containers\AppSection\Resume\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ResumeKind: string implements HasLabel, HasColor
{
    case Master = 'master';
    case Application = 'application';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Master => 'Основне',
            self::Application => 'Під вакансію',
        };
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Master => 'primary',
            self::Application => 'info',
        };
    }
}
