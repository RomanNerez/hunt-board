<?php

namespace App\Containers\AppSection\Resume\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResumeLocale: string implements HasLabel
{
    case English = 'en';
    case Ukrainian = 'uk';

    /**
     * @return self
     */
    public static function default(): self
    {
        return self::English;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Ukrainian => 'Українська',
        };
    }

    /**
     * @return string
     */
    public function fileSuffix(): string
    {
        return match ($this) {
            self::English => 'EN',
            self::Ukrainian => 'UA',
        };
    }
}
