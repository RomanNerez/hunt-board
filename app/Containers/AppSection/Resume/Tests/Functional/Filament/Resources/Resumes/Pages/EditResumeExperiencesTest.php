<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeExperiences;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditResumeExperiences::class)]
final class EditResumeExperiencesTest extends FunctionalTestCase
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
    public function testFillsExistingExperiencesInSavedOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw', 'employment_type' => 'full_time', 'sort_order' => 1]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Grammarly', 'sort_order' => 0, 'is_visible' => false]);

        $rows = array_values(Livewire::test(EditResumeExperiences::class, ['record' => $resume->id])->get('data.experiences'));

        $this->assertSame(['Grammarly', 'MacPaw'], array_column($rows, 'company'));
        $this->assertSame('full_time', $rows[1]['employment_type']);
        $this->assertStringStartsWith('2021-03-01', $rows[1]['start_date']);
        $this->assertFalse($rows[0]['is_visible']);
    }

    /**
     * @return void
     */
    public function testSavesReorderedAndChangedExperiencesAndStaysOnPage(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw', 'sort_order' => 0]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Grammarly', 'sort_order' => 1]);

        Livewire::test(EditResumeExperiences::class, ['record' => $resume->id])
            ->fillForm(['experiences' => [
                ['company' => 'Grammarly', 'position' => 'Dev', 'start_date' => '2019-01-01', 'is_visible' => true],
                ['company' => 'Preply', 'position' => 'Dev', 'start_date' => '2022-05-15', 'end_date' => '2023-01-10', 'employment_type' => 'full_time', 'is_visible' => false],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNoRedirect();

        $experiences = $resume->experiences()->get();

        $this->assertSame(['Grammarly', 'Preply'], $experiences->pluck('company')->all());
        $this->assertSame('2022-05-01', $experiences[1]->start_date->toDateString());
        $this->assertSame('full_time', $experiences[1]->employment_type?->value);
        $this->assertFalse($experiences[1]->is_visible);
    }

    /**
     * @return void
     */
    public function testFillsAndSavesExperienceStacks(): void
    {
        $php = Stack::factory()->createOne();
        $vue = Stack::factory()->createOne();
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        $experience = ResumeExperience::factory()->createOne(['resume_id' => $resume->id]);
        $experience->stacks()->attach([$vue->id => ['sort' => 0], $php->id => ['sort' => 1]]);

        $rows = array_values(Livewire::test(EditResumeExperiences::class, ['record' => $resume->id])->get('data.experiences'));

        $this->assertSame([$vue->id, $php->id], array_map(intval(...), $rows[0]['stacks']));

        $rows[0]['stacks'] = [$php->id];

        Livewire::test(EditResumeExperiences::class, ['record' => $resume->id])
            ->fillForm(['experiences' => $rows])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$php->id], $resume->experiences()->sole()->stacks->modelKeys());
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
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        $experience = array_merge(['company' => 'MacPaw', 'position' => 'Dev', 'start_date' => '2021-03-01'], $override);

        Livewire::test(EditResumeExperiences::class, ['record' => $resume->id])
            ->fillForm(['experiences' => [$experience]])
            ->call('save')
            ->assertHasFormErrors(["experiences.0.$field"]);

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
}
