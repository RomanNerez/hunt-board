<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\CreateProfileProjectAction;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Stack;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateProfileProjectAction::class)]
final class CreateProfileProjectActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesProjectForGivenWorkExperience(): void
    {
        $experience = ProfileWorkExperience::factory()->createOne();

        $record = app(CreateProfileProjectAction::class)->run($experience->id, [
            'name' => ['uk' => 'Особистий кабінет клієнта'],
            'started_on' => '2024-09-01',
            'finished_on' => null,
        ]);

        $this->assertDatabaseHas(ProfileProject::class, [
            'id' => $record->id,
            'work_experience_id' => $experience->id,
            'finished_on' => null,
        ]);
        $this->assertCount(0, $record->stacks()->get());
    }

    /**
     * @return void
     */
    public function testStackKeepsSelectionOrderAndFirstOfRepeats(): void
    {
        $experience = ProfileWorkExperience::factory()->createOne();
        $names = ['Laravel', 'Vue 2', 'Vuex', 'MySQL'];
        $ids = array_map(static fn (string $name): int => Stack::factory()->createOne(['name' => $name])->id, $names);

        $record = app(CreateProfileProjectAction::class)->run($experience->id, [
            'name' => ['uk' => 'Особистий кабінет клієнта'],
            'started_on' => '2024-09-01',
            'stacks' => [...$ids, (string) $ids[3]],
        ]);

        $this->assertSame($names, $record->stacks()->pluck('name')->all());
    }

    /**
     * @return void
     */
    public function testInvalidPeriodCreatesNothing(): void
    {
        $experience = ProfileWorkExperience::factory()->createOne();

        try {
            app(CreateProfileProjectAction::class)->run($experience->id, [
                'name' => ['uk' => 'Промо-лендинг'],
                'started_on' => '2020-09-01',
                'finished_on' => '2020-01-01',
            ]);
            $this->fail('Invalid period was accepted.');
        } catch (ValidationException) {
        }

        $this->assertDatabaseCount(ProfileProject::class, 0);
    }
}
