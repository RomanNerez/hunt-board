<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\UI\WEB\Controllers;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\WEB\Controllers\DownloadResumeController;
use App\Containers\AppSection\Profile\UI\WEB\Controllers\PreviewResumeController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

#[CoversClass(PreviewResumeController::class)]
#[CoversClass(DownloadResumeController::class)]
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
    public function testPreviewRespondsWithInlineUkrainianPdf(): void
    {
        $fake = Pdf::fake();
        $this->actingAs($this->userWithProfile());

        $this->get('/admin/profile/resume/uk/preview')->assertOk();

        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => $pdf->isInline()
            && 'Roman_Nerez_CV_UA.pdf' === $pdf->downloadName
            && str_contains($pdf->getHtml(), 'Технічні навички'));
    }

    /**
     * @return void
     */
    public function testDownloadRespondsWithAttachmentNamedAfterProfile(): void
    {
        $fake = Pdf::fake();
        $this->actingAs($this->userWithProfile());

        $this->get(route('profile.resume.download', ['locale' => 'uk']))->assertOk();

        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => $pdf->isDownload()
            && 'Roman_Nerez_CV_UA.pdf' === $pdf->downloadName);
    }

    /**
     * @return void
     */
    public function testEnglishPdfUsesEnglishLabels(): void
    {
        $fake = Pdf::fake();
        $this->actingAs($this->userWithProfile());

        $this->get('/admin/profile/resume/en/preview')->assertOk();

        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => 'Roman_Nerez_CV_EN.pdf' === $pdf->downloadName
            && str_contains($pdf->getHtml(), 'Technical Skills'));
    }

    /**
     * @param string $route
     *
     * @return void
     */
    #[DataProvider('routeProvider')]
    public function testUnsupportedLocaleIsNotFound(string $route): void
    {
        Pdf::fake();
        $this->actingAs($this->userWithProfile());

        $this->get("/admin/profile/resume/de/{$route}")->assertNotFound();
    }

    /**
     * @param string $route
     *
     * @return void
     */
    #[DataProvider('routeProvider')]
    public function testUserWithoutProfileGetsNotFound(string $route): void
    {
        Pdf::fake();
        $this->actingAs(User::factory()->createOne());

        $this->get("/admin/profile/resume/uk/{$route}")->assertNotFound();
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

        $this->get("/admin/profile/resume/uk/{$route}")->assertRedirect('/admin/login');
    }

    /**
     * @return void
     */
    public function testPdfContainsOnlyCurrentUsersData(): void
    {
        $fake = Pdf::fake();
        UserProfile::factory()->createOne(['first_name' => 'Роман', 'last_name' => 'Нерез']);
        $olena = UserProfile::factory()->createOne(['first_name' => 'Олена', 'last_name' => 'Коваль']);
        $this->actingAs(User::query()->findOrFail($olena->user_id));

        $this->get('/admin/profile/resume/uk/download')->assertOk();

        $fake->assertRespondedWithPdf(static fn (PdfBuilder $pdf): bool => str_contains($pdf->getHtml(), 'Олена')
            && !str_contains($pdf->getHtml(), 'Роман'));
    }

    /**
     * @return void
     */
    public function testPdfServiceErrorGivesServiceUnavailable(): void
    {
        config(['laravel-pdf.driver' => 'gotenberg', 'laravel-pdf.gotenberg.url' => 'http://gotenberg.test']);
        Http::fake(['gotenberg.test/*' => Http::response('Chromium failed', 500)]);
        $this->actingAs($this->userWithProfile());

        $this->get('/admin/profile/resume/uk/preview')->assertStatus(503);
    }

    /**
     * @return void
     */
    public function testUnreachablePdfServiceGivesServiceUnavailable(): void
    {
        config(['laravel-pdf.driver' => 'gotenberg', 'laravel-pdf.gotenberg.url' => 'http://gotenberg.test']);
        Http::fake(['gotenberg.test/*' => static fn () => throw new ConnectionException('Connection refused')]);
        $this->actingAs($this->userWithProfile());

        $this->get('/admin/profile/resume/uk/download')->assertStatus(503);
    }

    /**
     * @return User
     */
    private function userWithProfile(): User
    {
        $profile = UserProfile::factory()->createOne([
            'first_name' => ['uk' => 'Роман', 'en' => 'Roman'],
            'last_name' => ['uk' => 'Нерез', 'en' => 'Nerez'],
        ]);
        ProfileSkillGroup::factory()->withStacks()->createOne(['user_id' => $profile->user_id]);

        return User::query()->findOrFail($profile->user_id);
    }
}
