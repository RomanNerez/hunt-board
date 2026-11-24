<?php

namespace App\Containers\AppSection\Resume\Actions;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tasks\CreateResumeTask;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeLinksTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreateResumeAction extends ParentAction
{
    /**
     * @param CreateResumeTask $createResumeTask
     * @param ReplaceResumeLinksTask $replaceResumeLinksTask
     */
    public function __construct(
        private readonly CreateResumeTask $createResumeTask,
        private readonly ReplaceResumeLinksTask $replaceResumeLinksTask,
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

        return DB::transaction(function () use ($userId, $data, $hasLinks, $links): Resume {
            $resume = $this->createResumeTask->run($userId, $data);

            if ($hasLinks) {
                $this->replaceResumeLinksTask->run($resume, $links);
            }

            return $resume;
        });
    }
}
