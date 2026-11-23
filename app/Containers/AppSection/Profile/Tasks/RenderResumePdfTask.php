<?php

namespace App\Containers\AppSection\Profile\Tasks;

use App\Containers\AppSection\Profile\Enums\ResumeTemplate;
use App\Containers\AppSection\Profile\Values\Resume\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class RenderResumePdfTask extends ParentTask
{
    /**
     * @var float
     */
    private const VERTICAL_MARGIN_MM = 12.0;

    /**
     * @param Resume $resume
     * @param ResumeTemplate $template
     *
     * @return PdfBuilder
     */
    public function run(Resume $resume, ResumeTemplate $template): PdfBuilder
    {
        return Pdf::view($template->view(), ['resume' => $resume])
            ->format(Format::A4)
            ->margins(top: self::VERTICAL_MARGIN_MM, bottom: self::VERTICAL_MARGIN_MM, unit: Unit::Millimeter)
            ->name($resume->fileName);
    }
}
