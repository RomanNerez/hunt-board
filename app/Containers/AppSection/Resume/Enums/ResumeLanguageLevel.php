<?php

namespace App\Containers\AppSection\Resume\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResumeLanguageLevel: string implements HasLabel
{
    case A1 = 'a1';
    case A2 = 'a2';
    case B1 = 'b1';
    case B2 = 'b2';
    case C1 = 'c1';
    case C2 = 'c2';
    case Native = 'native';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::A1 => 'A1 Beginner',
            self::A2 => 'A2 Elementary',
            self::B1 => 'B1 Intermediate',
            self::B2 => 'B2 Upper-Intermediate',
            self::C1 => 'C1 Advanced',
            self::C2 => 'C2 Proficient',
            self::Native => 'Рідна',
        };
    }
}
