<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Support;

use App\Containers\AppSection\Vacancy\Enums\Currency;

final class SalaryRangeFormatter
{
    /**
     * @param int|null $min
     * @param int|null $max
     * @param Currency|null $currency
     *
     * @return string|null
     */
    public static function format(int|null $min, int|null $max, Currency|null $currency): string|null
    {
        $range = match (true) {
            null !== $min && null !== $max => self::amount($min) . '–' . self::amount($max),
            null !== $min => 'від ' . self::amount($min),
            null !== $max => 'до ' . self::amount($max),
            default => null,
        };

        if (null === $range) {
            return null;
        }

        return null === $currency ? $range : "{$range} {$currency->value}";
    }

    /**
     * @param int $amount
     *
     * @return string
     */
    private static function amount(int $amount): string
    {
        return number_format($amount, 0, '', ' ');
    }
}
