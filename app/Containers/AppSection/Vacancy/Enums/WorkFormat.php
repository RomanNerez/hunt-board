<?php

namespace App\Containers\AppSection\Vacancy\Enums;

use Filament\Support\Contracts\HasLabel;

enum WorkFormat: string implements HasLabel
{
    case REMOTE = 'remote';
    case HYBRID = 'hybrid';
    case OFFICE = 'office';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::REMOTE => 'Віддалено',
            self::HYBRID => 'Гібрид',
            self::OFFICE => 'Офіс',
        };
    }
}
