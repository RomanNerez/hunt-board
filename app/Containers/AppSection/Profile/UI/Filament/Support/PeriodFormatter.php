<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Support;

use Carbon\CarbonImmutable;

final class PeriodFormatter
{
    /**
     * @param CarbonImmutable $startedOn
     * @param CarbonImmutable|null $finishedOn
     *
     * @return string
     */
    public static function monthYear(CarbonImmutable $startedOn, CarbonImmutable|null $finishedOn): string
    {
        $finished = $finishedOn?->format('m.Y') ?? 'дотепер';

        return "{$startedOn->format('m.Y')} — {$finished}";
    }
}
