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
        $project = ProfileProject::factory()->createOne(['name' => 'Аукціон', 'technologies' => ['PHP']]);

        $result = app(UpdateProfileProjectAction::class)->run($project->id, ['name' => ['uk' => 'Аукціон з продажу автомобілів']]);

        $this->assertSame($project->id, $result->id);
        $this->assertSame('Аукціон з продажу автомобілів', $result->refresh()->name);
        $this->assertSame(['PHP'], $result->technologies);
    }
}
