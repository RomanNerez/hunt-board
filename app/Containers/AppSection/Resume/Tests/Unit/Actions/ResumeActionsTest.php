<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Actions;

use App\Containers\AppSection\Resume\Actions\CreateResumeAction;
use App\Containers\AppSection\Resume\Actions\DeleteResumeAction;
use App\Containers\AppSection\Resume\Actions\UpdateResumeAction;
use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\QueryException;
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

    /**
     * @return void
     */
    public function testCreateStoresLinksInOrder(): void
    {
        $user = User::factory()->createOne();

        $resume = app(CreateResumeAction::class)->run($user->id, [
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'links' => [
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/john'],
                ['label' => 'GitHub', 'url' => 'https://github.com/john'],
            ],
        ]);

        $this->assertSame(['LinkedIn', 'GitHub'], $resume->links()->pluck('label')->all());
    }

    /**
     * @return void
     */
    public function testUpdateReplacesLinksOnlyWhenKeyIsPresent(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'LinkedIn']);

        app(UpdateResumeAction::class)->run($resume->id, ['first_name' => 'Нове']);
        $this->assertSame(['LinkedIn'], $resume->links()->pluck('label')->all());

        app(UpdateResumeAction::class)->run($resume->id, ['links' => [['label' => 'Telegram', 'url' => 'https://t.me/john']]]);
        $this->assertSame(['Telegram'], $resume->links()->pluck('label')->all());
    }

    /**
     * @return void
     */
    public function testUpdateRollsBackResumeWhenLinksFail(): void
    {
        $resume = Resume::factory()->createOne(['first_name' => 'Старе']);

        try {
            app(UpdateResumeAction::class)->run($resume->id, ['first_name' => 'Нове', 'links' => [['label' => null, 'url' => 'https://a.com']]]);
            $this->fail('Exception expected');
        } catch (QueryException) {
        }

        $this->assertSame('Старе', $resume->refresh()->first_name);
    }

    /**
     * @return void
     */
    public function testCreateStoresExperiencesInOrder(): void
    {
        $user = User::factory()->createOne();

        $resume = app(CreateResumeAction::class)->run($user->id, [
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'experiences' => [
                ['company' => 'MacPaw', 'position' => 'Dev', 'start_date' => '2021-03-01'],
                ['company' => 'Grammarly', 'position' => 'Dev', 'start_date' => '2019-01-01'],
            ],
        ]);

        $this->assertSame(['MacPaw', 'Grammarly'], $resume->experiences()->pluck('company')->all());
    }

    /**
     * @return void
     */
    public function testUpdateReplacesExperiencesOnlyWhenKeyIsPresent(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw']);

        app(UpdateResumeAction::class)->run($resume->id, ['first_name' => 'Нове']);
        $this->assertSame(['MacPaw'], $resume->experiences()->pluck('company')->all());

        app(UpdateResumeAction::class)->run($resume->id, ['experiences' => [['company' => 'Preply', 'position' => 'Dev', 'start_date' => '2022-05-01']]]);
        $this->assertSame(['Preply'], $resume->experiences()->pluck('company')->all());
    }

    /**
     * @return void
     */
    public function testUpdateRollsBackResumeWhenExperiencesFail(): void
    {
        $resume = Resume::factory()->createOne(['first_name' => 'Старе']);

        try {
            app(UpdateResumeAction::class)->run($resume->id, ['first_name' => 'Нове', 'experiences' => [['company' => null, 'position' => 'Dev', 'start_date' => '2021-03-01']]]);
            $this->fail('Exception expected');
        } catch (QueryException) {
        }

        $this->assertSame('Старе', $resume->refresh()->first_name);
    }

    /**
     * @return void
     */
    public function testExperiencesKeepStacksInOrderAndReplaceDoesNotLeavePivotRows(): void
    {
        $php = Stack::factory()->createOne();
        $laravel = Stack::factory()->createOne();
        $resume = Resume::factory()->createOne();

        $action = app(UpdateResumeAction::class);

        $action->run($resume->id, ['experiences' => [
            ['company' => 'MacPaw', 'position' => 'Dev', 'start_date' => '2021-03-01', 'stacks' => [$laravel->id, $php->id]],
        ]]);
        $this->assertSame([$laravel->id, $php->id], $resume->experiences()->sole()->stacks->modelKeys());

        $action->run($resume->id, ['experiences' => [
            ['company' => 'MacPaw', 'position' => 'Dev', 'start_date' => '2021-03-01', 'stacks' => [$php->id]],
        ]]);

        $this->assertSame([$php->id], $resume->experiences()->sole()->stacks->modelKeys());
        $this->assertDatabaseCount('stackables', 1);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesExperienceStacks(): void
    {
        $resume = Resume::factory()->createOne();
        $experience = ResumeExperience::factory()->createOne(['resume_id' => $resume->id]);
        $experience->stacks()->attach(Stack::factory()->createOne()->id, ['sort' => 0]);

        app(DeleteResumeAction::class)->run($resume->id);

        $this->assertDatabaseCount('stackables', 0);
    }

    /**
     * @return void
     */
    public function testCreateStoresEducationsInOrder(): void
    {
        $user = User::factory()->createOne();

        $resume = app(CreateResumeAction::class)->run($user->id, [
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'educations' => [
                ['institution' => 'Braine Academy'],
                ['institution' => 'Prometheus'],
            ],
        ]);

        $this->assertSame(['Braine Academy', 'Prometheus'], $resume->educations()->pluck('institution')->all());
    }

    /**
     * @return void
     */
    public function testUpdateReplacesEducationsOnlyWhenKeyIsPresent(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Braine Academy']);

        app(UpdateResumeAction::class)->run($resume->id, ['first_name' => 'Нове']);
        $this->assertSame(['Braine Academy'], $resume->educations()->pluck('institution')->all());

        app(UpdateResumeAction::class)->run($resume->id, ['educations' => [['institution' => 'KPI']]]);
        $this->assertSame(['KPI'], $resume->educations()->pluck('institution')->all());
    }

    /**
     * @return void
     */
    public function testUpdateRollsBackResumeWhenEducationsFail(): void
    {
        $resume = Resume::factory()->createOne(['first_name' => 'Старе']);

        try {
            app(UpdateResumeAction::class)->run($resume->id, ['first_name' => 'Нове', 'educations' => [['institution' => null]]]);
            $this->fail('Exception expected');
        } catch (QueryException) {
        }

        $this->assertSame('Старе', $resume->refresh()->first_name);
    }
}
