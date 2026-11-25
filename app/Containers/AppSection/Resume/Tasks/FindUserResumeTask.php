<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Data\Repositories\ResumeRepository;
use App\Containers\AppSection\Resume\Exceptions\ResumePdfUnavailable;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindUserResumeTask extends ParentTask
{
    /**
     * @var list<string>
     */
    private const RELATIONS = [
        'links',
        'experiences.stacks',
        'educations',
        'languages',
    ];

    /**
     * @param ResumeRepository<Resume> $repository
     */
    public function __construct(
        private readonly ResumeRepository $repository,
    ) {
    }

    /**
     * @param int $userId
     * @param int $resumeId
     *
     * @return Resume
     *
     * @throws ResumePdfUnavailable
     */
    public function run(int $userId, int $resumeId): Resume
    {
        $resume = $this->repository
            ->with(self::RELATIONS)
            ->findWhere(['id' => $resumeId, 'user_id' => $userId])
            ->first();

        if (!$resume instanceof Resume) {
            throw ResumePdfUnavailable::becauseResumeIsMissing();
        }

        return $resume;
    }
}
