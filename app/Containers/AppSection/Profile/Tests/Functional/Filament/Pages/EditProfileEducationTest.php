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
            ->fillForm(['education' => [['type' => EducationType::COURSE->value, 'title' => ['uk' => 'Laravel From Scratch']]]])
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
            ->fillForm(['education' => [['type' => EducationType::CERTIFICATE->value, 'title' => ['uk' => 'Scrum Master'], 'finished_on' => '2023-05-10']]])
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
                'education.0.title.uk' => 'AWS Certified Developer — Associate',
                'education.1.title.uk' => "Комп'ютерні науки",
                'education.1.started_on' => '2016-09-01',
            ]);
    }

    /**
     * @return void
     */
    public function testTypeOffersThreeOptions(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->fillForm(['education' => [['title' => ['uk' => 'Новий запис']]]])
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
     * @return void
     */
    public function testOneRecordKeepsTitleInBothLanguages(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->set('dataLocale', 'en')
            ->fillForm(['education' => [[
                'type' => EducationType::UNIVERSITY->value,
                'title' => ['uk' => "Бакалавр комп'ютерних наук", 'en' => 'Bachelor of Computer Science'],
                'institution' => ['uk' => 'КПІ ім. Ігоря Сікорського', 'en' => 'Igor Sikorsky Kyiv Polytechnic Institute'],
            ]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $record = $this->storedEducation()->sole();

        $this->assertEquals(['uk' => "Бакалавр комп'ютерних наук", 'en' => 'Bachelor of Computer Science'], $record->getTranslations('title'));
        $this->assertEquals(['uk' => 'КПІ ім. Ігоря Сікорського', 'en' => 'Igor Sikorsky Kyiv Polytechnic Institute'], $record->getTranslations('institution'));

        Livewire::test(EditProfileEducation::class)
            ->assertSchemaStateSet([
                'education.0.title.uk' => "Бакалавр комп'ютерних наук",
                'education.0.title.en' => 'Bachelor of Computer Science',
                'education.0.description.en' => null,
            ]);
    }

    /**
     * @return void
     */
    public function testMissingUkrainianTitleWhileEnglishIsActiveSwitchesToUkrainian(): void
    {
        Livewire::test(EditProfileEducation::class)
            ->set('dataLocale', 'en')
            ->fillForm(['education' => [['type' => EducationType::COURSE->value, 'title' => ['uk' => '', 'en' => 'Laravel From Scratch']]]])
            ->call('save')
            ->assertHasFormErrors(['education.0.title.uk' => 'required'])
            ->assertSet('dataLocale', 'uk');

        $this->assertCount(0, $this->storedEducation());
    }

    /**
     * @return void
     */
    public function testItemHeaderShowsTitleOfActiveLanguageWithUkrainianFallback(): void
    {
        ProfileEducation::factory()->createOne([
            'user_id' => $this->user->id,
            'type' => EducationType::UNIVERSITY,
            'title' => ['uk' => "Бакалавр комп'ютерних наук", 'en' => 'Bachelor of Computer Science'],
            'sort' => 0,
        ]);
        ProfileEducation::factory()->createOne([
            'user_id' => $this->user->id,
            'type' => EducationType::COURSE,
            'title' => ['uk' => 'Курс Laravel'],
            'sort' => 1,
        ]);

        Livewire::test(EditProfileEducation::class)
            ->assertSee("Університет · Бакалавр комп'ютерних наук")
            ->set('dataLocale', 'en')
            ->assertSee('Університет · Bachelor of Computer Science')
            ->assertSee('Курс · Курс Laravel');
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidRecordProvider(): array
    {
        return [
            'missing title' => [['type' => 'course', 'title' => ['uk' => '']], 'title.uk', 'required'],
            'english title only' => [['type' => 'course', 'title' => ['uk' => '', 'en' => 'Bachelor of Computer Science']], 'title.uk', 'required'],
            'too long english title' => [['type' => 'course', 'title' => ['uk' => 'Курс', 'en' => str_repeat('a', 256)]], 'title.en', 'max'],
            'finished before started' => [['type' => 'university', 'title' => ['uk' => 'Магістратура'], 'started_on' => '2020-09-01', 'finished_on' => '2019-06-30'], 'finished_on', 'after_or_equal'],
            'invalid url' => [['type' => 'certificate', 'title' => ['uk' => 'Scrum'], 'url' => 'not a url'], 'url', 'url'],
        ];
    }

    /**
     * @param array<string, mixed> $invalidRecord
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
            ->fillForm(['education' => [['type' => 'university', 'title' => ['uk' => 'Магістратура'], 'started_on' => '2020-09-01', 'finished_on' => '2019-06-30']]])
            ->call('save');

        $this->assertContains('Поле дата завершення має містити дату не раніше дата початку.', $component->errors()->all());
    }

    /**
     * @return array<string, mixed>
     */
    private function university(): array
    {
        return [
            'type' => EducationType::UNIVERSITY->value,
            'title' => ['uk' => "Комп'ютерні науки"],
            'institution' => ['uk' => 'КПІ ім. Ігоря Сікорського'],
            'started_on' => '2016-09-01',
            'finished_on' => '2020-06-30',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function certificate(): array
    {
        return [
            'type' => EducationType::CERTIFICATE->value,
            'title' => ['uk' => 'AWS Certified Developer — Associate'],
            'institution' => ['uk' => 'Amazon Web Services'],
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
