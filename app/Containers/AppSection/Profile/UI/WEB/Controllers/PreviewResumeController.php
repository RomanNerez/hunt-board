<?php

namespace App\Containers\AppSection\Profile\UI\WEB\Controllers;

use App\Containers\AppSection\Profile\Actions\GenerateResumePdfAction;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Exceptions\ResumeNotAvailable;
use App\Containers\AppSection\Profile\UI\WEB\Controllers\Concerns\RespondsWithResumePdf;
use App\Containers\AppSection\Profile\UI\WEB\Requests\GetResumeRequest;
use App\Ship\Parents\Controllers\WebController;
use Symfony\Component\HttpFoundation\Response;

final class PreviewResumeController extends WebController
{
    use RespondsWithResumePdf;

    /**
     * @param GetResumeRequest $request
     * @param ProfileLocale $locale
     * @param GenerateResumePdfAction $action
     *
     * @return Response
     *
     * @throws ResumeNotAvailable
     */
    public function __invoke(GetResumeRequest $request, ProfileLocale $locale, GenerateResumePdfAction $action): Response
    {
        $pdf = $action->run($request->userId(), $locale);

        return $this->respondWithPdf($pdf->inline($pdf->downloadName), $request);
    }
}
