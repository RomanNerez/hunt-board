<?php

namespace App\Containers\AppSection\Profile\Tasks\Concerns;

use Carbon\CarbonImmutable;

trait ParsesDates
{
    /**
     * @param mixed $value
     *
     * @return CarbonImmutable|null
     */
    private function toDate(mixed $value): CarbonImmutable|null
    {
        if ($value instanceof \DateTimeInterface) {
            return CarbonImmutable::instance($value)->startOfDay();
        }

        if (is_string($value) && '' !== trim($value)) {
            return CarbonImmutable::parse($value)->startOfDay();
        }

        return null;
    }
}
