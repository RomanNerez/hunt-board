<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ReplaceVacancySourcesTask extends ParentTask
{
    /**
     * @param Vacancy $vacancy
     * @param list<array<string, mixed>> $sources
     *
     * @return void
     */
    public function run(Vacancy $vacancy, array $sources): void
    {
        $pivots = [];

        foreach ($sources as $source) {
            $pivots[$this->sourceId($source)] = ['url' => $this->url($source)];
        }

        $vacancy->vacancySources()->sync($pivots);
    }

    /**
     * @param array<string, mixed> $source
     *
     * @return int
     */
    private function sourceId(array $source): int
    {
        /** @var int|string $id */
        $id = $source['vacancy_source_id'];

        return (int) $id;
    }

    /**
     * @param array<string, mixed> $source
     *
     * @return string|null
     */
    private function url(array $source): string|null
    {
        $url = $source['url'] ?? null;

        return is_string($url) && '' !== trim($url) ? trim($url) : null;
    }
}
