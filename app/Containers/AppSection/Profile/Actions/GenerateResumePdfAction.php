<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Enums\ResumeTemplate;
use App\Containers\AppSection\Profile\Exceptions\ResumeNotAvailable;
use App\Containers\AppSection\Profile\Tasks\BuildResumeTask;
use App\Containers\AppSection\Profile\Tasks\RenderResumePdfTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Spatie\LaravelPdf\PdfBuilder;

final class GenerateResumePdfAction extends ParentAction
{
    /**
     * @param BuildResumeTask $buildResumeTask
     * @param RenderResumePdfTask $renderResumePdfTask
     */
    public function __construct(
        private readonly BuildResumeTask $buildResumeTask,
        private readonly RenderResumePdfTask $renderResumePdfTask,
    ) {
    }

    /**
     * @param int $userId
     * @param ProfileLocale $locale
     * @param ResumeTemplate $template
     *
     * @return PdfBuilder
     *
     * @throws ResumeNotAvailable
     */
    public function run(int $userId, ProfileLocale $locale, ResumeTemplate $template = ResumeTemplate::Classic): PdfBuilder
    {
        return $this->renderResumePdfTask->run($this->buildResumeTask->run($userId, $locale), $template);
    }
}
