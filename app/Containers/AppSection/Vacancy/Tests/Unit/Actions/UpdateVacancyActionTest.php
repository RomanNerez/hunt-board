<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Actions;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Vacancy\Actions\UpdateVacancyAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateVacancyAction::class)]
final class UpdateVacancyActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesVacancyStatus(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        $interview = Status::factory()->createOne(['name' => 'Interview']);
        $vacancy = Vacancy::factory()->createOne(['status_id' => $applied->id]);

        $result = app(UpdateVacancyAction::class)->run($vacancy->id, ['status_id' => $interview->id]);

        $this->assertTrue($result->relationLoaded('company'));
        $this->assertTrue($result->relationLoaded('vacancySource'));
        $this->assertSame('Interview', $result->status->name);
        $this->assertDatabaseHas(Vacancy::class, ['id' => $vacancy->id, 'status_id' => $interview->id]);
    }
}
