<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileEducation;
use App\Containers\AppSection\User\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditProfileEducation::class)]
final class EditProfileEducationTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var \Closure
     */
    private \Closure $undoRepeaterFake;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->undoRepeaterFake = Repeater::fake();
        $this->user = User::factory()->createOne();
        $this->actingAs($this->user);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        ($this->undoRepeaterFake)();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testStoresUniversityAndCertificateInEnteredOrder(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [
                $this->university(),
                $this->certificate(),
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $records = $this->storedEducation();

        $this->assertCount(2, $records);
        $this->assertSame(EducationType::UNIVERSITY, $records[0]->type);
        $this->assertSame('КПІ ім. Ігоря Сікорського', $records[0]->institution);
        $this->assertSame('2016-09-01', $records[0]->started_on?->toDateString());
        $this->assertSame('2020-06-30', $records[0]->finished_on?->toDateString());
        $this->assertSame(EducationType::CERTIFICATE, $records[1]->type);
        $this->assertSame('https://www.credly.com/badges/aws-developer', $records[1]->url);
    }

    /**
     * @return void
     */
    public function testStoresCourseWithTypeAndTitleOnly(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [['type' => EducationType::COURSE->value, 'title' => 'Laravel From Scratch']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $course = $this->storedEducation()->sole();

        $this->assertSame('Laravel From Scratch', $course->title);
        $this->assertNull($course->institution);
        $this->assertNull($course->started_on);
        $this->assertNull($course->finished_on);
        $this->assertNull($course->url);
        $this->assertNull($course->description);
    }

    /**
     * @return void
     */
    public function testAcceptsFinishDateWithoutStartDate(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [['type' => EducationType::CERTIFICATE->value, 'title' => 'Scrum Master', 'finished_on' => '2023-05-10']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('2023-05-10', $this->storedEducation()->sole()->finished_on?->toDateString());
    }

    /**
     * @return void
     */
    public function testRemovingRecordKeepsTheOther(): void
    {
        $this->seedEducation();

        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [$this->certificate()]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([EducationType::CERTIFICATE], $this->storedEducation()->pluck('type')->all());
    }

    /**
     * @return void
     */
    public function testNewOrderIsShownAfterReopening(): void
    {
        $this->seedEducation();

        Livewire::test(EditProfileEducation::class)
            ->assertSchemaStateSet(['education.0.type' => EducationType::UNIVERSITY, 'education.1.type' => EducationType::CERTIFICATE])
            ->fillForm(['education' => [$this->certificate(), $this->university()]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileEducation::class)
            ->assertSchemaStateSet([
                'education.0.title' => 'AWS Certified Developer — Associate',
                'education.1.title' => "Комп'ютерні науки",
                'education.1.started_on' => '2016-09-01',
            ]);
    }

    /**
     * @return void
     */
    public function testTypeOffersThreeOptions(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [['title' => 'Новий запис']]])
            ->assertFormFieldExists('education.0.type', static fn (Select $field): bool => ['Університет', 'Курс', 'Сертифікат'] === array_values($field->getOptions()));
    }

    /**
     * @return void
     */
    public function testItemHeaderShowsTypeAndTitle(): void
    {
        ProfileEducation::factory()->createOne([
            'user_id' => $this->user->id,
            'type' => EducationType::CERTIFICATE,
            'title' => 'AWS Certified Developer — Associate',
        ]);

        Livewire::test(EditProfileEducation::class)
            ->assertSee('Сертифікат · AWS Certified Developer — Associate');
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidRecordProvider(): array
    {
        return [
            'missing title' => [['type' => 'course', 'title' => ''], 'title', 'required'],
            'finished before started' => [['type' => 'university', 'title' => 'Магістратура', 'started_on' => '2020-09-01', 'finished_on' => '2019-06-30'], 'finished_on', 'after_or_equal'],
            'invalid url' => [['type' => 'certificate', 'title' => 'Scrum', 'url' => 'not a url'], 'url', 'url'],
        ];
    }

    /**
     * @param array<string, string> $invalidRecord
     * @param string $field
     * @param string $rule
     *
     * @return void
     */
    #[DataProvider('invalidRecordProvider')]
    public function testInvalidRecordKeepsStoredList(array $invalidRecord, string $field, string $rule): void
    {
        $this->seedEducation();

        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [$this->university(), $invalidRecord]])
            ->call('save')
            ->assertHasFormErrors(["education.1.{$field}" => $rule])
            ->assertNotNotified('Профіль збережено');

        $this->assertSame([EducationType::UNIVERSITY, EducationType::CERTIFICATE], $this->storedEducation()->pluck('type')->all());
    }

    /**
     * @return void
     */
    public function testFinishedBeforeStartedShowsUkrainianMessage(): void
    {
        $component = Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [['type' => 'university', 'title' => 'Магістратура', 'started_on' => '2020-09-01', 'finished_on' => '2019-06-30']]])
            ->call('save');

        $this->assertContains('Поле дата завершення має містити дату не раніше дата початку.', $component->errors()->all());
    }

    /**
     * @return array<string, string>
     */
    private function university(): array
    {
        return [
            'type' => EducationType::UNIVERSITY->value,
            'title' => "Комп'ютерні науки",
            'institution' => 'КПІ ім. Ігоря Сікорського',
            'started_on' => '2016-09-01',
            'finished_on' => '2020-06-30',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function certificate(): array
    {
        return [
            'type' => EducationType::CERTIFICATE->value,
            'title' => 'AWS Certified Developer — Associate',
            'institution' => 'Amazon Web Services',
            'url' => 'https://www.credly.com/badges/aws-developer',
        ];
    }

    /**
     * @return void
     */
    private function seedEducation(): void
    {
        ProfileEducation::factory()->createOne(['user_id' => $this->user->id, 'sort' => 0, ...$this->university()]);
        ProfileEducation::factory()->createOne(['user_id' => $this->user->id, 'sort' => 1, ...$this->certificate()]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ProfileEducation>
     */
    private function storedEducation(): \Illuminate\Database\Eloquent\Collection
    {
        return ProfileEducation::query()->where('user_id', $this->user->id)->orderBy('sort')->get();
    }
}
