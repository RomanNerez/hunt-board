<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeResource::class)]
final class ResumeResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/resumes')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testHasIndexCreateAndEditPagesOnly(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(ResumeResource::getPages()));
    }

    /**
     * @return void
     */
    public function testPagesAreServedAtResumesPath(): void
    {
        $user = User::factory()->createOne();
        $resume = Resume::factory()->createOne(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->assertSame(url('/admin/resumes'), ResumeResource::getUrl('index'));
        $this->get('/admin/resumes')->assertOk()->assertSee('Резюме');
        $this->get('/admin/resumes/create')->assertOk();
        $this->get("/admin/resumes/{$resume->id}/edit")->assertOk();
    }

    /**
     * @return void
     */
    public function testEditingForeignResumeReturnsNotFound(): void
    {
        $foreign = Resume::factory()->createOne();
        $this->actingAs(User::factory()->createOne());

        $this->get("/admin/resumes/{$foreign->id}/edit")->assertNotFound();
    }
}
