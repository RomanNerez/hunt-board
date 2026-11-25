<?php

namespace App\Containers\AppSection\Resume\Actions;

use App\Containers\AppSection\Resume\Exceptions\ResumePdfUnavailable;
use App\Containers\AppSection\Resume\Tasks\BuildResumeDocumentTask;
use App\Containers\AppSection\Resume\Tasks\FindUserResumeTask;
use App\Containers\AppSection\Resume\Tasks\RenderResumePdfTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Spatie\LaravelPdf\PdfBuilder;

final class GenerateResumePdfAction extends ParentAction
{
    /**
     * @param FindUserResumeTask $findUserResumeTask
     * @param BuildResumeDocumentTask $buildResumeDocumentTask
     * @param RenderResumePdfTask $renderResumePdfTask
     */
    public function __construct(
        private readonly FindUserResumeTask $findUserResumeTask,
        private readonly BuildResumeDocumentTask $buildResumeDocumentTask,
        private readonly RenderResumePdfTask $renderResumePdfTask,
    ) {
    }

    /**
     * @param int $userId
     * @param int $resumeId
     *
     * @return PdfBuilder
     *
     * @throws ResumePdfUnavailable
     */
    public function run(int $userId, int $resumeId): PdfBuilder
    {
        $resume = $this->findUserResumeTask->run($userId, $resumeId);

        return $this->renderResumePdfTask->run($this->buildResumeDocumentTask->run($resume));
    }
}
