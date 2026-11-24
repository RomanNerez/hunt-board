<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Actions;

use App\Containers\AppSection\Resume\Actions\CreateResumeAction;
use App\Containers\AppSection\Resume\Actions\DeleteResumeAction;
use App\Containers\AppSection\Resume\Actions\UpdateResumeAction;
use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateResumeAction::class)]
#[CoversClass(UpdateResumeAction::class)]
#[CoversClass(DeleteResumeAction::class)]
final class ResumeActionsTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreateStoresResumeForUser(): void
    {
        $user = User::factory()->createOne();

        $resume = app(CreateResumeAction::class)->run($user->id, [
            'kind' => ResumeKind::Master,
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'headline' => 'PHP Developer',
            'summary' => 'Опис',
        ]);

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'user_id' => $user->id, 'kind' => 'master', 'headline' => 'PHP Developer']);
    }

    /**
     * @return void
     */
    public function testUpdateDoesNotChangeOwner(): void
    {
        $resume = Resume::factory()->createOne();
        $owner = $resume->user_id;

        app(UpdateResumeAction::class)->run($resume->id, [
            'user_id' => User::factory()->createOne()->id,
            'first_name' => 'Нове',
        ]);

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'user_id' => $owner, 'first_name' => 'Нове']);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesResume(): void
    {
        $resume = Resume::factory()->createOne();

        $this->assertTrue(app(DeleteResumeAction::class)->run($resume->id));
        $this->assertModelMissing($resume);
    }
}
