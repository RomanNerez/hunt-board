<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\UpdateProfileProjectTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateProfileProjectTask::class)]
final class UpdateProfileProjectTaskTest extends UnitTestCase
{
    /**
     * @var ProfileProject
     */
    private ProfileProject $project;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->project = ProfileProject::factory()->createOne([
            'name' => ['uk' => 'Аукціон', 'en' => 'Auction'],
            'started_on' => '2019-09-03',
            'finished_on' => '2020-08-21',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatesTranslationsAndSharedFields(): void
    {
        $result = app(UpdateProfileProjectTask::class)->run($this->project->id, [
            'name' => ['uk' => 'Аукціон з продажу автомобілів', 'en' => ''],
            'technologies' => ['Laravel', 'Laravel'],
            'description' => ['uk' => '<p>Опис</p>'],
            'finished_on' => null,
        ])->refresh();

        $this->assertSame($this->project->id, $result->id);
        $this->assertSame(['uk' => 'Аукціон з продажу автомобілів'], $result->getTranslations('name'));
        $this->assertSame(['Laravel'], $result->technologies);
        $this->assertSame('<p>Опис</p>', $result->description);
        $this->assertNull($result->finished_on);
    }

    /**
     * @return void
     */
    public function testIgnoresWorkExperienceChangeInData(): void
    {
        $other = ProfileWorkExperience::factory()->createOne();

        app(UpdateProfileProjectTask::class)->run($this->project->id, [
            'work_experience_id' => $other->id,
            'name' => ['uk' => 'Промо-лендинг'],
        ]);

        $this->assertDatabaseHas(ProfileProject::class, ['id' => $this->project->id, 'work_experience_id' => $this->project->work_experience_id]);
        $this->assertDatabaseMissing(ProfileProject::class, ['work_experience_id' => $other->id]);
    }

    /**
     * @return void
     */
    public function testRejectsFinishBeforeStoredStart(): void
    {
        try {
            app(UpdateProfileProjectTask::class)->run($this->project->id, ['finished_on' => '2019-01-01']);
            $this->fail('Finish date before start was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('finished_on', $exception->errors());
        }

        $this->assertSame('2020-08-21', $this->project->refresh()->finished_on?->toDateString());
    }
}
