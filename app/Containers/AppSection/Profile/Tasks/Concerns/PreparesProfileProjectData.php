<?php

namespace App\Containers\AppSection\Profile\Tasks\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait PreparesProfileProjectData
{
    use NormalizesTranslations;
    use ParsesDates;

    /**
     * @var list<string>
     */
    private static array $projectAttributes = ['name', 'started_on', 'finished_on', 'description'];

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function projectAttributes(array $data): array
    {
        /** @var array<string, mixed> $attributes */
        $attributes = Arr::only($data, self::$projectAttributes);
        $attributes = $this->normalizeTranslations($attributes, ['name']);

        if (array_key_exists('description', $attributes)) {
            $attributes['description'] = $this->normalizeTranslation($this->sanitizeRichText($attributes['description']));
        }

        return $attributes;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function sanitizeRichText(mixed $value): mixed
    {
        if (!is_array($value)) {
            return is_string($value) ? $this->sanitizeRichTextValue($value) : $value;
        }

        return array_map(fn (mixed $text): mixed => is_string($text) ? $this->sanitizeRichTextValue($text) : $text, $value);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function sanitizeRichTextValue(string $html): string
    {
        $sanitized = Str::sanitizeHtml($html);

        return '' === trim(html_entity_decode(strip_tags($sanitized))) ? '' : $sanitized;
    }

    /**
     * @param mixed $startedOn
     * @param mixed $finishedOn
     *
     * @return void
     *
     * @throws ValidationException
     */
    private function assertPeriodOrder(mixed $startedOn, mixed $finishedOn): void
    {
        $started = $this->toDate($startedOn);
        $finished = $this->toDate($finishedOn);

        if (null === $started) {
            throw ValidationException::withMessages(['started_on' => __('validation.required', ['attribute' => 'дата початку'])]);
        }

        if (null !== $finished && $finished->lessThan($started)) {
            throw ValidationException::withMessages(['finished_on' => __('validation.after_or_equal', ['attribute' => 'дата завершення', 'date' => 'дата початку'])]);
        }
    }
}
