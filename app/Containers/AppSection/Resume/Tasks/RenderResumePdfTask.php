<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocument;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class RenderResumePdfTask extends ParentTask
{
    /**
     * @var string
     */
    private const VIEW = 'appSection@resume::pdf.classic';

    /**
     * @var float
     */
    private const VERTICAL_MARGIN_MM = 12.0;

    /**
     * @param ResumeDocument $document
     *
     * @return PdfBuilder
     */
    public function run(ResumeDocument $document): PdfBuilder
    {
        return Pdf::view(self::VIEW, ['resume' => $document])
            ->format(Format::A4)
            ->margins(top: self::VERTICAL_MARGIN_MM, bottom: self::VERTICAL_MARGIN_MM, unit: Unit::Millimeter)
            ->name($document->fileName);
    }
}
