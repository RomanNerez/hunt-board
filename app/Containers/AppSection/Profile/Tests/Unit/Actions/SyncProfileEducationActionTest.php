<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\SyncProfileEducationAction;
use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileEducationTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncProfileEducationAction::class)]
#[CoversClass(ReplaceProfileEducationTask::class)]
final class SyncProfileEducationActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testStoresAllTypesWithAndWithoutDatesInOrder(): void
    {
        $user = User::factory()->createOne();

        app(SyncProfileEducationAction::class)->run($user->id, [
            [
                'type' => EducationType::UNIVERSITY->value,
                'title' => "Комп'ютерні науки",
                'institution' => 'КПІ ім. Ігоря Сікорського',
                'started_on' => '2016-09-01',
                'finished_on' => '2020-06-30',
            ],
            [
                'type' => EducationType::CERTIFICATE->value,
                'title' => 'AWS Certified Developer — Associate',
                'institution' => 'Amazon Web Services',
                'url' => 'https://aws.amazon.com/certification/verify',
            ],
            ['type' => EducationType::COURSE->value, 'title' => 'Laravel From Scratch'],
        ]);

        $records = ProfileEducation::query()->where('user_id', $user->id)->orderBy('sort')->get();

        $this->assertSame([EducationType::UNIVERSITY, EducationType::CERTIFICATE, EducationType::COURSE], $records->pluck('type')->all());
        $this->assertSame([0, 1, 2], $records->pluck('sort')->all());
        $this->assertSame('2016-09-01', $records[0]->started_on?->toDateString());
        $this->assertSame('2020-06-30', $records[0]->finished_on?->toDateString());
        $this->assertSame('https://aws.amazon.com/certification/verify', $records[1]->url);
        $this->assertNull($records[2]->institution);
        $this->assertNull($records[2]->started_on);
        $this->assertNull($records[2]->finished_on);
        $this->assertNull($records[2]->url);
        $this->assertNull($records[2]->description);
    }

    /**
     * @return void
     */
    public function testReplacesOnlyOwnRecords(): void
    {
        $user = User::factory()->createOne();
        ProfileEducation::factory()->createOne(['user_id' => $user->id, 'title' => 'Старий курс']);
        $foreign = ProfileEducation::factory()->createOne();

        app(SyncProfileEducationAction::class)->run($user->id, [
            ['type' => EducationType::COURSE->value, 'title' => 'Новий курс'],
        ]);

        $this->assertSame(['Новий курс'], ProfileEducation::query()->where('user_id', $user->id)->pluck('title')->all());
        $this->assertModelExists($foreign);
    }
}
