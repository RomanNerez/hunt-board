<?php

namespace App\Containers\AppSection\Resume\UI\WEB\Controllers;

use App\Containers\AppSection\Resume\Actions\GenerateResumePdfAction;
use App\Containers\AppSection\Resume\Exceptions\ResumePdfUnavailable;
use App\Containers\AppSection\Resume\UI\WEB\Controllers\Concerns\RespondsWithResumePdf;
use App\Ship\Parents\Controllers\WebController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreviewResumePdfController extends WebController
{
    use RespondsWithResumePdf;

    /**
     * @param Request $request
     * @param int $resume
     * @param GenerateResumePdfAction $action
     *
     * @return Response
     *
     * @throws ResumePdfUnavailable
     */
    public function __invoke(Request $request, int $resume, GenerateResumePdfAction $action): Response
    {
        $pdf = $action->run($this->currentUserId($request), $resume);

        return $this->respondWithPdf($pdf->inline($pdf->downloadName), $request);
    }
}
