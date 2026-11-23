<?php

namespace App\Containers\AppSection\Profile\UI\WEB\Controllers\Concerns;

use App\Containers\AppSection\Profile\Exceptions\ResumeNotAvailable;
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
     * @throws ResumeNotAvailable
     */
    private function respondWithPdf(PdfBuilder $pdf, Request $request): Response
    {
        try {
            return $pdf->toResponse($request);
        } catch (CouldNotGeneratePdf|ConnectionException $exception) {
            throw ResumeNotAvailable::becausePdfServiceFailed($exception);
        }
    }
}
