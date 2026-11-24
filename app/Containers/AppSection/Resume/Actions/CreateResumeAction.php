<?php

namespace App\Containers\AppSection\Resume\Actions;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tasks\CreateResumeTask;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeEducationsTask;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeExperiencesTask;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeLinksTask;
use App\Containers\AppSection\Stack\Tasks\SyncStacksTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreateResumeAction extends ParentAction
{
    /**
     * @param CreateResumeTask $createResumeTask
     * @param ReplaceResumeLinksTask $replaceResumeLinksTask
     * @param ReplaceResumeExperiencesTask $replaceResumeExperiencesTask
     * @param SyncStacksTask $syncStacksTask
     * @param ReplaceResumeEducationsTask $replaceResumeEducationsTask
     */
    public function __construct(
        private readonly CreateResumeTask $createResumeTask,
        private readonly ReplaceResumeLinksTask $replaceResumeLinksTask,
        private readonly ReplaceResumeExperiencesTask $replaceResumeExperiencesTask,
        private readonly SyncStacksTask $syncStacksTask,
        private readonly ReplaceResumeEducationsTask $replaceResumeEducationsTask,
    ) {
    }

    /**
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return Resume
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $data): Resume
    {
        $hasLinks = array_key_exists('links', $data);

        /** @var list<array<string, mixed>> $links */
        $links = Arr::pull($data, 'links') ?? [];

        $hasExperiences = array_key_exists('experiences', $data);

        /** @var list<array<string, mixed>> $experiences */
        $experiences = Arr::pull($data, 'experiences') ?? [];

        $hasEducations = array_key_exists('educations', $data);

        /** @var list<array<string, mixed>> $educations */
        $educations = Arr::pull($data, 'educations') ?? [];

        return DB::transaction(function () use ($userId, $data, $hasLinks, $links, $hasExperiences, $experiences, $hasEducations, $educations): Resume {
            $resume = $this->createResumeTask->run($userId, $data);

            if ($hasLinks) {
                $this->replaceResumeLinksTask->run($resume, $links);
            }

            if ($hasExperiences) {
                $created = $this->replaceResumeExperiencesTask->run($resume, $experiences);

                foreach ($created as $position => $experience) {
                    $this->syncStacksTask->run($experience, $experiences[$position]['stacks'] ?? []);
                }
            }

            if ($hasEducations) {
                $this->replaceResumeEducationsTask->run($resume, $educations);
            }

            return $resume;
        });
    }
}
