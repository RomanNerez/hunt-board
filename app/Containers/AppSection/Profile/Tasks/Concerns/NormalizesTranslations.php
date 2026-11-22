<?php

namespace App\Containers\AppSection\Profile\Tasks\Concerns;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;

trait NormalizesTranslations
{
    /**
     * @param array<string, mixed> $attributes
     * @param list<string> $fields
     *
     * @return array<string, mixed>
     */
    private function normalizeTranslations(array $attributes, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $attributes)) {
                $attributes[$field] = $this->normalizeTranslation($attributes[$field]);
            }
        }

        return $attributes;
    }

    /**
     * @param mixed $value
     *
     * @return array<string, string>|null
     */
    private function normalizeTranslation(mixed $value): array|null
    {
        $translations = is_array($value) ? $value : [ProfileLocale::primary()->value => $value];
        $normalized = [];

        foreach (ProfileLocale::values() as $locale) {
            $text = is_string($translations[$locale] ?? null) ? trim($translations[$locale]) : '';

            if ('' !== $text) {
                $normalized[$locale] = $text;
            }
        }

        return [] === $normalized ? null : $normalized;
    }
}
