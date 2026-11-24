<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeLanguages;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditResumeLanguages::class)]
final class EditResumeLanguagesTest extends FunctionalTestCase
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
    public function testFillsExistingLanguagesInSavedOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Англійська', 'level' => 'b2', 'level_label' => 'Intermediate', 'sort_order' => 1]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Українська', 'sort_order' => 0, 'is_visible' => false]);

        $rows = array_values(Livewire::test(EditResumeLanguages::class, ['record' => $resume->id])->get('data.languages'));

        $this->assertSame(['Українська', 'Англійська'], array_column($rows, 'language'));
        $this->assertSame('b2', $rows[1]['level']);
        $this->assertSame('Intermediate', $rows[1]['level_label']);
        $this->assertFalse($rows[0]['is_visible']);
    }

    /**
     * @return void
     */
    public function testSavesReorderedAndChangedLanguagesAndStaysOnPage(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Українська', 'sort_order' => 0]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Англійська', 'sort_order' => 1]);

        Livewire::test(EditResumeLanguages::class, ['record' => $resume->id])
            ->fillForm(['languages' => [
                ['language' => 'Англійська', 'level' => 'c1', 'is_visible' => true],
                ['language' => 'Українська', 'level' => 'native', 'is_visible' => true],
                ['language' => 'Польська', 'level_label' => 'Розмовна', 'is_visible' => false],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNoRedirect();

        $languages = $resume->languages()->get();

        $this->assertSame(['Англійська', 'Українська', 'Польська'], $languages->pluck('language')->all());
        $this->assertSame('c1', $languages[0]->level?->value);
        $this->assertFalse($languages[2]->is_visible);
    }

    /**
     * @return void
     */
    public function testSameLanguageIsAllowedInAnotherResume(): void
    {
        $other = Resume::factory()->createOne();
        ResumeLanguage::factory()->createOne(['resume_id' => $other->id, 'language' => 'Англійська']);
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResumeLanguages::class, ['record' => $resume->id])
            ->fillForm(['languages' => [['language' => 'Англійська', 'is_visible' => true]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Англійська'], $resume->languages()->pluck('language')->all());
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
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResumeLanguages::class, ['record' => $resume->id])
            ->fillForm(['languages' => $languages])
            ->call('save')
            ->assertHasFormErrors([$errorKey]);

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
