<?php

namespace App\Containers\AppSection\Vacancy\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case USD = 'USD';
    case EUR = 'EUR';
    case UAH = 'UAH';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->value;
    }
}
