<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\UI\WEB\Controllers;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\WEB\Controllers\DownloadResumePdfController;
use App\Containers\AppSection\Resume\UI\WEB\Controllers\PreviewResumePdfController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

#[CoversClass(PreviewResumePdfController::class)]
#[CoversClass(DownloadResumePdfController::class)]
final class ResumePdfRoutesTest extends FunctionalTestCase
{
    /**
     * @return list<array{string}>
     */
    public static function routeProvider(): array
    {
        return [
            ['preview'],
            ['download'],
        ];
    }

    /**
     * @return void
     */
    public function testPreviewRespondsWithInlinePdfThatIsNotCached(): void
    {
        $fake = Pdf::fake();
        $resume = $this->actingAsOwnerOf(Resume::factory()->createOne(['first_name' => 'Roman', 'last_name' => 'Nerez']));
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw']);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Hidden Corp', 'is_visible' => false]);

        $response = $this->get("/admin/resumes/{$resume->id}/pdf/preview")->assertOk();

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => $pdf->isInline()
            && 'Roman_Nerez_CV_EN.pdf' === $pdf->downloadName
            && str_contains($pdf->getHtml(), 'Work Experience')
            && str_contains($pdf->getHtml(), 'MacPaw')
            && !str_contains($pdf->getHtml(), 'Hidden Corp'));
    }

    /**
     * @return void
     */
    public function testDownloadRespondsWithAttachmentNamedAfterResume(): void
    {
        $fake = Pdf::fake();
        $resume = $this->actingAsOwnerOf(Resume::factory()->createOne(['first_name' => 'Roman', 'last_name' => 'Nerez']));

        $response = $this->get(route('resumes.pdf.download', ['resume' => $resume->id]))->assertOk();

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => $pdf->isDownload()
            && 'Roman_Nerez_CV_EN.pdf' === $pdf->downloadName);
    }

    /**
     * @return void
     */
    public function testUkrainianResumeUsesUkrainianLabelsAndSuffix(): void
    {
        $fake = Pdf::fake();
        $resume = $this->actingAsOwnerOf(Resume::factory()->ukrainian()->createOne(['first_name' => 'Роман', 'last_name' => 'Нерез']));
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'start_date' => '2024-01-01', 'end_date' => null]);

        $this->get("/admin/resumes/{$resume->id}/pdf/download")->assertOk();

        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => str_ends_with((string) $pdf->downloadName, '_CV_UA.pdf')
            && str_contains($pdf->getHtml(), 'Досвід роботи')
            && str_contains($pdf->getHtml(), 'Дотепер'));
    }

    /**
     * @param string $route
     *
     * @return void
     */
    #[DataProvider('routeProvider')]
    public function testForeignResumeIsNotFound(string $route): void
    {
        Pdf::fake();
        $foreign = Resume::factory()->createOne();
        $this->actingAs(User::factory()->createOne());

        $this->get("/admin/resumes/{$foreign->id}/pdf/{$route}")->assertNotFound();
    }

    /**
     * @param string $route
     *
     * @return void
     */
    #[DataProvider('routeProvider')]
    public function testMissingResumeIsNotFound(string $route): void
    {
        Pdf::fake();
        $this->actingAs(User::factory()->createOne());

        $this->get("/admin/resumes/999999/pdf/{$route}")->assertNotFound();
    }

    /**
     * @param string $route
     *
     * @return void
     */
    #[DataProvider('routeProvider')]
    public function testGuestIsRedirectedToLogin(string $route): void
    {
        Pdf::fake();
        $resume = Resume::factory()->createOne();

        $this->get("/admin/resumes/{$resume->id}/pdf/{$route}")->assertRedirect('/admin/login');
    }

    /**
     * @return void
     */
    public function testPdfServiceErrorGivesServiceUnavailable(): void
    {
        config(['laravel-pdf.driver' => 'gotenberg', 'laravel-pdf.gotenberg.url' => 'http://gotenberg.test']);
        Http::fake(['gotenberg.test/*' => Http::response('Chromium failed', 500)]);
        $resume = $this->actingAsOwnerOf(Resume::factory()->createOne());

        $this->get("/admin/resumes/{$resume->id}/pdf/preview")->assertStatus(503);
    }

    /**
     * @return void
     */
    public function testUnreachablePdfServiceGivesServiceUnavailable(): void
    {
        config(['laravel-pdf.driver' => 'gotenberg', 'laravel-pdf.gotenberg.url' => 'http://gotenberg.test']);
        Http::fake(['gotenberg.test/*' => static fn () => throw new ConnectionException('Connection refused')]);
        $resume = $this->actingAsOwnerOf(Resume::factory()->createOne());

        $this->get("/admin/resumes/{$resume->id}/pdf/download")->assertStatus(503);
    }

    /**
     * @param Resume $resume
     *
     * @return Resume
     */
    private function actingAsOwnerOf(Resume $resume): Resume
    {
        $this->actingAs(User::query()->findOrFail($resume->user_id));

        return $resume;
    }
}
