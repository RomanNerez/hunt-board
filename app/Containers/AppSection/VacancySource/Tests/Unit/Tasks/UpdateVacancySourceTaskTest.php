<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Tasks;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tasks\UpdateVacancySourceTask;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateVacancySourceTask::class)]
final class UpdateVacancySourceTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesVacancySourceName(): void
    {
        $record = VacancySource::factory()->createOne(['name' => 'Linkedin']);

        $result = app(UpdateVacancySourceTask::class)->run($record->id, ['name' => 'LinkedIn']);

        $this->assertSame($record->id, $result->id);
        $this->assertDatabaseHas(VacancySource::class, ['id' => $record->id, 'name' => 'LinkedIn']);
    }
}
