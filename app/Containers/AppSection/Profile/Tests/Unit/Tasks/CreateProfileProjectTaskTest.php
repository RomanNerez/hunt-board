<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\CreateProfileProjectTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateProfileProjectTask::class)]
final class CreateProfileProjectTaskTest extends UnitTestCase
{
    /**
     * @var ProfileWorkExperience
     */
    private ProfileWorkExperience $experience;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->experience = ProfileWorkExperience::factory()->createOne(['started_on' => '2020-08-29', 'finished_on' => '2020-10-12']);
    }

    /**
     * @return void
     */
    public function testStoresBothLanguagesForGivenWorkExperience(): void
    {
        $record = app(CreateProfileProjectTask::class)->run($this->experience->id, [
            ...$this->validData(),
            'name' => ['uk' => ' Аукціон з продажу автомобілів ', 'en' => 'Car auction'],
            'description' => ['uk' => '<p>Міграція бази даних</p><ul><li><p>10 млн записів</p></li></ul>', 'en' => '<p>Database migration</p>'],
        ])->refresh();

        $this->assertSame($this->experience->id, $record->work_experience_id);
        $this->assertEquals(['uk' => 'Аукціон з продажу автомобілів', 'en' => 'Car auction'], $record->getTranslations('name'));
        $this->assertEquals(['uk' => '<p>Міграція бази даних</p><ul><li><p>10 млн записів</p></li></ul>', 'en' => '<p>Database migration</p>'], $record->getTranslations('description'));
        $this->assertSame('2020-08-29', $record->started_on->toDateString());
        $this->assertSame('2020-10-12', $record->finished_on?->toDateString());
    }

    /**
     * @return void
     */
    public function testBlankEnglishFallsBackToUkrainian(): void
    {
        $record = app(CreateProfileProjectTask::class)->run($this->experience->id, [
            ...$this->validData(),
            'name' => ['uk' => 'Промо-лендинг', 'en' => '  '],
            'description' => ['uk' => '<p>Запущено промо-лендинг</p>', 'en' => '<p></p>'],
        ])->refresh();

        $this->assertSame(['uk' => 'Промо-лендинг'], $record->getTranslations('name'));
        $this->assertSame(['uk' => '<p>Запущено промо-лендинг</p>'], $record->getTranslations('description'));

        App::setLocale('en');

        $this->assertSame('<p>Запущено промо-лендинг</p>', $record->description);
    }

    /**
     * @return void
     */
    public function testDescriptionIsSanitizedAndEmptyEditorIsStoredAsNull(): void
    {
        $sanitized = app(CreateProfileProjectTask::class)->run($this->experience->id, [
            ...$this->validData(),
            'description' => ['uk' => '<p>Опис<script>alert(1)</script></p><img src=x onerror="alert(1)">'],
        ])->refresh();
        $empty = app(CreateProfileProjectTask::class)->run($this->experience->id, [
            ...$this->validData(),
            'description' => ['uk' => '<p> </p>', 'en' => '<p></p>'],
        ])->refresh();

        $this->assertIsString($sanitized->description);
        $this->assertStringContainsString('Опис', $sanitized->description);
        $this->assertStringNotContainsString('script', $sanitized->description);
        $this->assertStringNotContainsString('onerror', $sanitized->description);
        $this->assertNull($empty->description);
        $this->assertDatabaseHas(ProfileProject::class, ['id' => $empty->id, 'description' => null]);
    }

    /**
     * @return void
     */
    public function testIgnoresOwnerAndUnknownAttributesFromData(): void
    {
        $other = ProfileWorkExperience::factory()->createOne();

        $record = app(CreateProfileProjectTask::class)->run($this->experience->id, [
            ...$this->validData(),
            'work_experience_id' => $other->id,
            'id' => 999,
            'role' => ['uk' => 'Junior розробник'],
        ]);

        $this->assertSame($this->experience->id, $record->work_experience_id);
        $this->assertNotSame(999, $record->id);
        $this->assertDatabaseMissing(ProfileProject::class, ['work_experience_id' => $other->id]);
    }

    /**
     * @return void
     */
    public function testDatesAreIndependentOfWorkExperiencePeriod(): void
    {
        $outside = app(CreateProfileProjectTask::class)->run($this->experience->id, [...$this->validData(), 'started_on' => '2019-06-01', 'finished_on' => '2021-10-01']);
        $current = app(CreateProfileProjectTask::class)->run($this->experience->id, [...$this->validData(), 'finished_on' => null]);

        $this->assertSame('2019-06-01', $outside->refresh()->started_on->toDateString());
        $this->assertNull($current->refresh()->finished_on);
    }

    /**
     * @return void
     */
    public function testRejectsFinishBeforeStart(): void
    {
        try {
            app(CreateProfileProjectTask::class)->run($this->experience->id, [...$this->validData(), 'started_on' => '2020-10-01', 'finished_on' => '2020-09-01']);
            $this->fail('Finish date before start was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame(['finished_on' => ['Поле дата завершення має містити дату не раніше дата початку.']], $exception->errors());
        }

        $this->assertDatabaseCount(ProfileProject::class, 0);
    }

    /**
     * @return void
     */
    public function testRejectsMissingStartDate(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateProfileProjectTask::class)->run($this->experience->id, [...$this->validData(), 'started_on' => null]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'name' => ['uk' => 'Аукціон з продажу автомобілів'],
            'started_on' => '2020-08-29',
            'finished_on' => '2020-10-12',
        ];
    }
}
