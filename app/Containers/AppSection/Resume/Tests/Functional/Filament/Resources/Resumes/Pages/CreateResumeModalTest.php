<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Actions\CreateResumeModalAction;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\ListResumes;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CreateResumeModalAction::class)]
final class CreateResumeModalTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
        $this->actingAs($this->user);
    }

    /**
     * @return void
     */
    public function testCreatesResumeForCurrentUserAndRedirectsToEditPage(): void
    {
        Livewire::test(ListResumes::class)
            ->callAction('create', ['first_name' => 'Роман', 'last_name' => 'Нерез'])
            ->assertHasNoFormErrors();

        $resume = Resume::query()->sole();

        $this->assertSame($this->user->id, $resume->user_id);
        $this->assertSame(ResumeKind::Master, $resume->kind);
        $this->assertSame('Роман', $resume->first_name);
        $this->assertNull($resume->headline);
        $this->assertNull($resume->summary);
    }

    /**
     * @return void
     */
    public function testRedirectsToEditPageOfCreatedResume(): void
    {
        Livewire::test(ListResumes::class)
            ->callAction('create', ['first_name' => 'Роман', 'last_name' => 'Нерез'])
            ->assertRedirect(ResumeResource::getUrl('edit', ['record' => Resume::query()->sole()]));
    }

    /**
     * @return void
     */
    public function testModalHasOnlyRequiredNameFields(): void
    {
        Livewire::test(ListResumes::class)
            ->mountAction('create')
            ->assertSchemaComponentExists('first_name')
            ->assertSchemaComponentExists('last_name')
            ->assertSchemaComponentDoesNotExist('headline')
            ->assertSchemaComponentDoesNotExist('summary');
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidData(): array
    {
        return [
            'без імені' => [['first_name' => ''], 'first_name'],
            'без прізвища' => [['last_name' => ''], 'last_name'],
            'задовге імʼя' => [['first_name' => str_repeat('а', 256)], 'first_name'],
        ];
    }

    /**
     * @param array<string, mixed> $override
     * @param string $field
     *
     * @return void
     */
    #[DataProvider('invalidData')]
    public function testRejectsInvalidData(array $override, string $field): void
    {
        Livewire::test(ListResumes::class)
            ->callAction('create', ['first_name' => 'Роман', 'last_name' => 'Нерез', ...$override])
            ->assertHasFormErrors([$field]);

        $this->assertDatabaseCount(Resume::class, 0);
    }

    /**
     * @return void
     */
    public function testEmptyStateActionOpensTheSameModal(): void
    {
        Livewire::test(ListResumes::class)
            ->mountAction(TestAction::make('create')->table())
            ->assertSchemaComponentExists('first_name')
            ->assertSchemaComponentExists('last_name');
    }
}
