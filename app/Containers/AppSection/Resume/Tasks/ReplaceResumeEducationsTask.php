<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Carbon\CarbonImmutable;

final class ReplaceResumeEducationsTask extends ParentTask
{
    /**
     * @param Resume $resume
     * @param list<array<string, mixed>> $educations
     *
     * @return void
     */
    public function run(Resume $resume, array $educations): void
    {
        $resume->educations()->delete();

        foreach ($educations as $position => $education) {
            $resume->educations()->create([
                'institution' => $education['institution'],
                'institution_url' => $this->text($education, 'institution_url'),
                'degree' => $this->text($education, 'degree'),
                'field_of_study' => $this->text($education, 'field_of_study'),
                'location' => $this->text($education, 'location'),
                'start_date' => $this->month($education['start_date'] ?? null),
                'end_date' => $this->month($education['end_date'] ?? null),
                'description' => $this->text($education, 'description'),
                'sort_order' => $position,
                'is_visible' => (bool) ($education['is_visible'] ?? true),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $education
     * @param string $key
     *
     * @return string|null
     */
    private function text(array $education, string $key): string|null
    {
        $value = $education[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : null;
    }

    /**
     * @param mixed $value
     *
     * @return CarbonImmutable|null
     */
    private function month(mixed $value): CarbonImmutable|null
    {
        if (!is_string($value) || '' === $value) {
            return null;
        }

        return CarbonImmutable::parse($value)->startOfMonth();
    }
}
