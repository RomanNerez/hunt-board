<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateResumeExperienceProjectsTask extends ParentTask
{
    /**
     * @param ResumeExperience $experience
     * @param list<array<string, mixed>> $projects
     *
     * @return list<ResumeExperienceProject>
     */
    public function run(ResumeExperience $experience, array $projects): array
    {
        $created = [];

        foreach ($projects as $position => $project) {
            $created[] = $experience->projects()->create([
                'name' => $project['name'],
                'role' => $this->text($project, 'role'),
                'description' => $this->text($project, 'description'),
                'sort_order' => $position,
                'is_visible' => (bool) ($project['is_visible'] ?? true),
            ]);
        }

        return $created;
    }

    /**
     * @param array<string, mixed> $project
     * @param string $key
     *
     * @return string|null
     */
    private function text(array $project, string $key): string|null
    {
        $value = $project[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : null;
    }
}
