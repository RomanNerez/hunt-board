<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\CreateResume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas\ResumeForm;
use App\Containers\AppSection\Stack\Models\Stack;
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

    /**
     * @return void
     */
    public function testCreatesResumeWithLinks(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm([
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'links' => [
                    ['icon' => 'fab-github', 'label' => 'GitHub', 'url' => 'https://github.com/john', 'is_visible' => true],
                    ['icon' => null, 'label' => 'Пошта', 'url' => 'mailto:john@mail.com', 'is_visible' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $links = Resume::query()->sole()->links;

        $this->assertSame(['GitHub', 'Пошта'], $links->pluck('label')->all());
        $this->assertSame('fab-github', $links[0]->icon);
        $this->assertFalse($links[1]->is_visible);
    }

    /**
     * @param array<string, mixed> $link
     * @param string $field
     *
     * @return void
     */
    #[DataProvider('invalidLinks')]
    public function testRejectsInvalidLinks(array $link, string $field): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'links' => [$link]])
            ->call('create')
            ->assertHasFormErrors(["links.0.$field"]);

        $this->assertSame(0, Resume::query()->count());
        $this->assertSame(0, ResumeLink::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidLinks(): array
    {
        return [
            'без підпису' => [['label' => '', 'url' => 'https://a.com'], 'label'],
            'без адреси' => [['label' => 'A', 'url' => ''], 'url'],
            'javascript' => [['label' => 'A', 'url' => 'javascript:alert(1)'], 'url'],
            'без схеми' => [['label' => 'A', 'url' => 'linkedin.com/in/john'], 'url'],
            'довгий підпис' => [['label' => str_repeat('a', 256), 'url' => 'https://a.com'], 'label'],
            'довга адреса' => [['label' => 'A', 'url' => 'https://a.com/' . str_repeat('a', 2048)], 'url'],
        ];
    }

    /**
     * @param string $url
     *
     * @return void
     */
    #[DataProvider('allowedUrls')]
    public function testAcceptsAllowedSchemes(string $url): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'links' => [['label' => 'A', 'url' => $url]]])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function allowedUrls(): array
    {
        return [
            'https' => ['https://a.com'],
            'http' => ['http://a.com'],
            'mailto' => ['mailto:a@a.com'],
            'tel' => ['tel:+380501234567'],
        ];
    }

    /**
     * @return void
     */
    public function testCreatesResumeWithExperiences(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm([
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'experiences' => [
                    [
                        'company' => 'MacPaw',
                        'company_url' => 'https://macpaw.com',
                        'position' => 'Senior PHP-розробник',
                        'employment_type' => 'full_time',
                        'start_date' => '2021-03-01',
                        'end_date' => '2024-08-01',
                        'is_visible' => true,
                    ],
                    ['company' => 'Grammarly', 'position' => 'Dev', 'start_date' => '2019-01-01', 'is_visible' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $experiences = Resume::query()->sole()->experiences;

        $this->assertSame(['MacPaw', 'Grammarly'], $experiences->pluck('company')->all());
        $this->assertSame('2021-03-01', $experiences[0]->start_date->toDateString());
        $this->assertSame('full_time', $experiences[0]->employment_type?->value);
        $this->assertNull($experiences[1]->end_date);
        $this->assertFalse($experiences[1]->is_visible);
    }

    /**
     * @return void
     */
    public function testCreatesExperienceWithStacks(): void
    {
        $php = Stack::factory()->createOne();
        $vue = Stack::factory()->createOne();

        Livewire::test(CreateResume::class)
            ->fillForm([
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'experiences' => [
                    ['company' => 'MacPaw', 'position' => 'Dev', 'start_date' => '2021-03-01', 'stacks' => [$vue->id, $php->id]],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame([$vue->id, $php->id], Resume::query()->sole()->experiences->sole()->stacks->modelKeys());
    }

    /**
     * @param array<string, mixed> $override
     * @param string $field
     *
     * @return void
     */
    #[DataProvider('invalidExperiences')]
    public function testRejectsInvalidExperiences(array $override, string $field): void
    {
        $experience = array_merge(['company' => 'MacPaw', 'position' => 'Dev', 'start_date' => '2021-03-01'], $override);

        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'experiences' => [$experience]])
            ->call('create')
            ->assertHasFormErrors(["experiences.0.$field"]);

        $this->assertSame(0, Resume::query()->count());
        $this->assertSame(0, ResumeExperience::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidExperiences(): array
    {
        return [
            'без компанії' => [['company' => ''], 'company'],
            'без посади' => [['position' => ''], 'position'],
            'без дати початку' => [['start_date' => null], 'start_date'],
            'кінець раніше початку' => [['end_date' => '2020-01-01'], 'end_date'],
            'javascript' => [['company_url' => 'javascript:alert(1)'], 'company_url'],
            'без схеми' => [['company_url' => 'macpaw.com'], 'company_url'],
            'довга компанія' => [['company' => str_repeat('a', 256)], 'company'],
            'довга посада' => [['position' => str_repeat('a', 256)], 'position'],
            'довга локація' => [['location' => str_repeat('a', 256)], 'location'],
            'довга адреса' => [['company_url' => 'https://a.com/' . str_repeat('a', 2048)], 'company_url'],
            'невідомий тип' => [['employment_type' => 'unknown'], 'employment_type'],
        ];
    }

    /**
     * @return void
     */
    public function testCreatesResumeWithEducations(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm([
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'educations' => [
                    [
                        'institution' => 'Braine Academy',
                        'institution_url' => 'https://brainecollege.com',
                        'degree' => 'Молодший спеціаліст',
                        'start_date' => '2016-09-01',
                        'end_date' => '2020-06-01',
                        'is_visible' => true,
                    ],
                    ['institution' => 'Prometheus', 'field_of_study' => 'PHP', 'is_visible' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $educations = Resume::query()->sole()->educations;

        $this->assertSame(['Braine Academy', 'Prometheus'], $educations->pluck('institution')->all());
        $this->assertSame('2016-09-01', $educations[0]->start_date?->toDateString());
        $this->assertNull($educations[1]->start_date);
        $this->assertNull($educations[1]->end_date);
        $this->assertFalse($educations[1]->is_visible);
    }

    /**
     * @return void
     */
    public function testAcceptsEducationWithSingleDate(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'educations' => [['institution' => 'KPI', 'end_date' => '2016-06-01']]])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, ResumeEducation::query()->count());
    }

    /**
     * @param array<string, mixed> $override
     * @param string $field
     *
     * @return void
     */
    #[DataProvider('invalidEducations')]
    public function testRejectsInvalidEducations(array $override, string $field): void
    {
        $education = array_merge(['institution' => 'Braine Academy'], $override);

        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'educations' => [$education]])
            ->call('create')
            ->assertHasFormErrors(["educations.0.$field"]);

        $this->assertSame(0, Resume::query()->count());
        $this->assertSame(0, ResumeEducation::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidEducations(): array
    {
        return [
            'без закладу' => [['institution' => ''], 'institution'],
            'кінець раніше початку' => [['start_date' => '2020-01-01', 'end_date' => '2019-01-01'], 'end_date'],
            'javascript' => [['institution_url' => 'javascript:alert(1)'], 'institution_url'],
            'без схеми' => [['institution_url' => 'brainecollege.com'], 'institution_url'],
            'довгий заклад' => [['institution' => str_repeat('a', 256)], 'institution'],
            'довгий ступінь' => [['degree' => str_repeat('a', 256)], 'degree'],
            'довга спеціальність' => [['field_of_study' => str_repeat('a', 256)], 'field_of_study'],
            'довга локація' => [['location' => str_repeat('a', 256)], 'location'],
            'довга адреса' => [['institution_url' => 'https://a.com/' . str_repeat('a', 2048)], 'institution_url'],
        ];
    }

    /**
     * @return void
     */
    public function testCreatesResumeWithLanguages(): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm([
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'languages' => [
                    ['language' => 'Українська', 'level' => 'native', 'is_visible' => true],
                    ['language' => 'Англійська', 'level' => 'b2', 'level_label' => 'Intermediate', 'is_visible' => false],
                    ['language' => 'Польська', 'level_label' => 'Розмовна', 'is_visible' => true],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $languages = Resume::query()->sole()->languages;

        $this->assertSame(['Українська', 'Англійська', 'Польська'], $languages->pluck('language')->all());
        $this->assertSame('native', $languages[0]->level?->value);
        $this->assertSame('Intermediate', $languages[1]->level_label);
        $this->assertFalse($languages[1]->is_visible);
        $this->assertNull($languages[2]->level);
    }

    /**
     * @param list<array<string, mixed>> $languages
     * @param string $errorKey
     *
     * @return void
     */
    #[DataProvider('invalidLanguages')]
    public function testRejectsInvalidLanguages(array $languages, string $errorKey): void
    {
        Livewire::test(CreateResume::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'languages' => $languages])
            ->call('create')
            ->assertHasFormErrors([$errorKey]);

        $this->assertSame(0, Resume::query()->count());
        $this->assertSame(0, ResumeLanguage::query()->count());
    }

    /**
     * @return array<string, array{list<array<string, mixed>>, string}>
     */
    public static function invalidLanguages(): array
    {
        return [
            'без назви' => [[['language' => '']], 'languages.0.language'],
            'довга назва' => [[['language' => str_repeat('a', 256)]], 'languages.0.language'],
            'довгий підпис' => [[['language' => 'Англійська', 'level_label' => str_repeat('a', 256)]], 'languages.0.level_label'],
            'невідомий рівень' => [[['language' => 'Англійська', 'level' => 'z9']], 'languages.0.level'],
            'дублікат' => [[['language' => 'Англійська'], ['language' => 'Англійська']], 'languages.1.language'],
            'дублікат без регістру' => [[['language' => 'Англійська'], ['language' => ' англійська ']], 'languages.1.language'],
        ];
    }
}
