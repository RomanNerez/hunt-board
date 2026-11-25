<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Actions;

use App\Containers\AppSection\Resume\Actions\GenerateResumePdfAction;
use App\Containers\AppSection\Resume\Exceptions\ResumePdfUnavailable;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tasks\RenderResumePdfTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocument;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\LaravelPdf\Facades\Pdf;

#[CoversClass(GenerateResumePdfAction::class)]
#[CoversClass(RenderResumePdfTask::class)]
final class GenerateResumePdfActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Pdf::fake();
    }

    /**
     * @return void
     */
    public function testBuildsA4PdfFromClassicViewNamedAfterResume(): void
    {
        $resume = Resume::factory()->ukrainian()->createOne(['first_name' => 'Roman', 'last_name' => 'Nerez']);

        $pdf = app(GenerateResumePdfAction::class)->run($resume->user_id, $resume->id);

        $this->assertSame('appSection@resume::pdf.classic', $pdf->viewName);
        $this->assertSame('a4', $pdf->format);
        $this->assertSame('Roman_Nerez_CV_UA.pdf', $pdf->downloadName);
        $this->assertSame(['top' => 12.0, 'right' => 0.0, 'bottom' => 12.0, 'left' => 0.0, 'unit' => 'mm'], $pdf->margins);

        $document = $pdf->viewData['resume'] ?? null;
        $this->assertInstanceOf(ResumeDocument::class, $document);
        $this->assertSame('Roman Nerez', $document->fullName);
    }

    /**
     * @return void
     */
    public function testForeignResumeThrowsResumePdfUnavailable(): void
    {
        $resume = Resume::factory()->createOne();

        $this->expectException(ResumePdfUnavailable::class);

        app(GenerateResumePdfAction::class)->run(User::factory()->createOne()->id, $resume->id);
    }
}
