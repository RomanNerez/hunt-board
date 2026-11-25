<?php

namespace App\Containers\AppSection\Resume\UI\WEB\Controllers\Concerns;

use App\Containers\AppSection\Resume\Exceptions\ResumePdfUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Exceptions\CouldNotGeneratePdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\Response;

trait RespondsWithResumePdf
{
    /**
     * @param PdfBuilder $pdf
     * @param Request $request
     *
     * @return Response
     *
     * @throws ResumePdfUnavailable
     */
    private function respondWithPdf(PdfBuilder $pdf, Request $request): Response
    {
        try {
            $response = $pdf->toResponse($request);
        } catch (CouldNotGeneratePdf|ConnectionException $exception) {
            throw ResumePdfUnavailable::becausePdfServiceFailed($exception);
        }

        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    private function currentUserId(Request $request): int
    {
        $id = $request->user()?->getAuthIdentifier();

        return is_numeric($id) ? (int) $id : 0;
    }
}
