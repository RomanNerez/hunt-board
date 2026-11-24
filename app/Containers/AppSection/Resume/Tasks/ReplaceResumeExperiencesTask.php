<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Carbon\CarbonImmutable;

final class ReplaceResumeExperiencesTask extends ParentTask
{
    /**
     * @param Resume $resume
     * @param list<array<string, mixed>> $experiences
     *
     * @return list<ResumeExperience>
     */
    public function run(Resume $resume, array $experiences): array
    {
        ResumeExperience::detachStacksFrom($resume->experiences()->getQuery());
        $resume->experiences()->delete();

        $created = [];

        foreach ($experiences as $position => $experience) {
            $created[] = $resume->experiences()->create([
                'company' => $experience['company'],
                'company_url' => $this->text($experience, 'company_url'),
                'position' => $experience['position'],
                'location' => $this->text($experience, 'location'),
                'employment_type' => $this->text($experience, 'employment_type'),
                'start_date' => $this->month($experience['start_date']),
                'end_date' => $this->month($experience['end_date'] ?? null),
                'description' => $this->text($experience, 'description'),
                'sort_order' => $position,
                'is_visible' => (bool) ($experience['is_visible'] ?? true),
            ]);
        }

        return $created;
    }

    /**
     * @param array<string, mixed> $experience
     * @param string $key
     *
     * @return string|null
     */
    private function text(array $experience, string $key): string|null
    {
        $value = $experience[$key] ?? null;

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

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
