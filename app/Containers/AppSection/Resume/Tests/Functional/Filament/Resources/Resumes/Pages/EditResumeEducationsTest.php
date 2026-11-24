<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeEducations;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditResumeEducations::class)]
final class EditResumeEducationsTest extends FunctionalTestCase
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
    public function testFillsExistingEducationsInSavedOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Braine Academy', 'degree' => 'Бакалавр', 'start_date' => '2016-09-01', 'sort_order' => 1]);
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Prometheus', 'sort_order' => 0, 'is_visible' => false]);

        $rows = array_values(Livewire::test(EditResumeEducations::class, ['record' => $resume->id])->get('data.educations'));

        $this->assertSame(['Prometheus', 'Braine Academy'], array_column($rows, 'institution'));
        $this->assertSame('Бакалавр', $rows[1]['degree']);
        $this->assertStringStartsWith('2016-09-01', $rows[1]['start_date']);
        $this->assertNull($rows[0]['start_date']);
        $this->assertFalse($rows[0]['is_visible']);
    }

    /**
     * @return void
     */
    public function testSavesReorderedAndChangedEducationsAndStaysOnPage(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Braine Academy', 'sort_order' => 0]);
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Prometheus', 'sort_order' => 1]);

        Livewire::test(EditResumeEducations::class, ['record' => $resume->id])
            ->fillForm(['educations' => [
                ['institution' => 'Prometheus', 'is_visible' => true],
                ['institution' => 'KPI', 'start_date' => '2012-09-15', 'end_date' => '2016-06-10', 'is_visible' => false],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNoRedirect();

        $educations = $resume->educations()->get();

        $this->assertSame(['Prometheus', 'KPI'], $educations->pluck('institution')->all());
        $this->assertSame('2012-09-01', $educations[1]->start_date?->toDateString());
        $this->assertFalse($educations[1]->is_visible);
    }

    /**
     * @return void
     */
    public function testAcceptsEducationWithSingleDate(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResumeEducations::class, ['record' => $resume->id])
            ->fillForm(['educations' => [['institution' => 'KPI', 'end_date' => '2016-06-01']]])
            ->call('save')
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
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        $education = array_merge(['institution' => 'Braine Academy'], $override);

        Livewire::test(EditResumeEducations::class, ['record' => $resume->id])
            ->fillForm(['educations' => [$education]])
            ->call('save')
            ->assertHasFormErrors(["educations.0.$field"]);

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
}
