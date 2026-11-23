<?php

namespace App\Containers\AppSection\Profile\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResumeTemplate: string implements HasLabel
{
    case Classic = 'classic';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Classic => 'Класичний',
        };
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return "appSection@profile::resume.templates.{$this->value}";
    }
}
