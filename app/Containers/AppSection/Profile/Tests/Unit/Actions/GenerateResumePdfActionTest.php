<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\GenerateResumePdfAction;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Enums\ResumeTemplate;
use App\Containers\AppSection\Profile\Exceptions\ResumeNotAvailable;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tasks\RenderResumePdfTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Profile\Values\Resume\Resume;
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
    public function testBuildsA4PdfFromTemplateViewWithResumeFileName(): void
    {
        $profile = UserProfile::factory()->createOne([
            'first_name' => ['uk' => 'Роман', 'en' => 'Roman'],
            'last_name' => ['uk' => 'Нерез', 'en' => 'Nerez'],
        ]);

        $pdf = app(GenerateResumePdfAction::class)->run($profile->user_id, ProfileLocale::English);

        $this->assertSame(ResumeTemplate::Classic->view(), $pdf->viewName);
        $this->assertSame('a4', $pdf->format);
        $this->assertSame('Roman_Nerez_CV_EN.pdf', $pdf->downloadName);
        $this->assertSame(['top' => 12.0, 'right' => 0.0, 'bottom' => 12.0, 'left' => 0.0, 'unit' => 'mm'], $pdf->margins);

        $resume = $pdf->viewData['resume'] ?? null;
        $this->assertInstanceOf(Resume::class, $resume);
        $this->assertSame(ProfileLocale::English, $resume->locale);
        $this->assertSame('Roman Nerez', $resume->fullName);
    }

    /**
     * @return void
     */
    public function testMissingProfileThrowsResumeNotAvailable(): void
    {
        $user = User::factory()->createOne();

        $this->expectException(ResumeNotAvailable::class);

        app(GenerateResumePdfAction::class)->run($user->id, ProfileLocale::Ukrainian);
    }
}
