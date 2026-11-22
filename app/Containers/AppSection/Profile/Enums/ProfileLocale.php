<?php

namespace App\Containers\AppSection\Profile\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProfileLocale: string implements HasLabel
{
    case Ukrainian = 'uk';
    case English = 'en';

    /**
     * @return self
     */
    public static function primary(): self
    {
        return self::Ukrainian;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $locale): string => $locale->value, self::cases());
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return self::primary() === $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Ukrainian => 'Українська',
            self::English => 'English',
        };
    }
}
