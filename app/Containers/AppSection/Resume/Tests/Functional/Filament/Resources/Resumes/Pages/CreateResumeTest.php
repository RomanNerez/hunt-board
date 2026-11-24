<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\CreateResume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas\ResumeForm;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CreateResume::class)]
#[CoversClass(ResumeForm::class)]
final class CreateResumeTest extends FunctionalTestCase
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
    public function testFormHasNoKindField(): void
    {
        Livewire::test(CreateResume::class)->assertFormFieldDoesNotExist('kind');
    }

    /**
     * @return void
     */
    public function testCreatesResumeForCurrentUserAndRedirectsToList(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm([
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'headline' => 'PHP Developer',
                'summary' => 'Короткий опис',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(ResumeResource::getUrl('index'));

        $resume = Resume::query()->sole();

        $this->assertSame($this->user->id, $resume->user_id);
        $this->assertSame(ResumeKind::Master, $resume->kind);
        $this->assertSame('Роман', $resume->first_name);
        $this->assertSame('PHP Developer', $resume->headline);
    }

    /**
     * @return void
     */
    public function testCreatesResumeWithRequiredFieldsOnly(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез'])
            ->call('create')
            ->assertHasNoFormErrors();

        $resume = Resume::query()->sole();

        $this->assertNull($resume->headline);
        $this->assertNull($resume->summary);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'empty first name' => [['first_name' => ''], 'first_name', 'required'],
            'empty last name' => [['last_name' => ''], 'last_name', 'required'],
            'too long headline' => [['headline' => str_repeat('а', 256)], 'headline', 'max'],
            'too long first name' => [['first_name' => str_repeat('а', 256)], 'first_name', 'max'],
        ];
    }

    /**
     * @param array<string, mixed> $override
     * @param string $field
     * @param string $rule
     *
     * @return void
     */
    #[DataProvider('invalidDataProvider')]
    public function testRejectsInvalidData(array $override, string $field, string $rule): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', ...$override])
            ->call('create')
            ->assertHasFormErrors([$field => $rule]);

        $this->assertDatabaseCount(Resume::class, 0);
    }
}
