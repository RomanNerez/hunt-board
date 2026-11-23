<?php

namespace App\Containers\AppSection\Profile\Tasks\Concerns;

trait NormalizesStringList
{
    /**
     * @param mixed $values
     *
     * @return list<string>
     */
    private function normalizeStringList(mixed $values): array
    {
        $items = [];

        foreach (is_array($values) ? $values : [] as $value) {
            $item = is_string($value) ? trim($value) : '';

            if ('' !== $item) {
                $items[] = $item;
            }
        }

        return array_values(array_unique($items));
    }
}
