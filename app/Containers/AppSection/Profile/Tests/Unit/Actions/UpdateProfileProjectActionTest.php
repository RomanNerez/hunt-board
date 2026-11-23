<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\UpdateProfileProjectAction;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateProfileProjectAction::class)]
final class UpdateProfileProjectActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesNameOfProject(): void
    {
        $project = ProfileProject::factory()->withStacks(2)->createOne(['name' => 'Аукціон']);
        $stack = $project->stacks()->pluck('stacks.id')->all();

        $result = app(UpdateProfileProjectAction::class)->run($project->id, ['name' => ['uk' => 'Аукціон з продажу автомобілів']]);

        $this->assertSame($project->id, $result->id);
        $this->assertSame('Аукціон з продажу автомобілів', $result->refresh()->name);
        $this->assertSame($stack, $result->stacks()->pluck('stacks.id')->all());
    }

    /**
     * @return void
     */
    public function testReplacesStackWhenGiven(): void
    {
        $project = ProfileProject::factory()->withStacks(3)->createOne();
        [$postgres, $mysql, $jquery] = $project->stacks()->get()->all();

        $result = app(UpdateProfileProjectAction::class)->run($project->id, ['stacks' => [$jquery->id, $postgres->id]]);

        $this->assertSame([$jquery->id, $postgres->id], $result->stacks()->pluck('stacks.id')->all());
        $this->assertModelExists($mysql);
    }
}
