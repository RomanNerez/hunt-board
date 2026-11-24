<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Tasks;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tasks\DeleteVacancySourceTask;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteVacancySourceTask::class)]
final class DeleteVacancySourceTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesVacancySource(): void
    {
        $record = VacancySource::factory()->createOne();

        $result = app(DeleteVacancySourceTask::class)->run($record->id);

        $this->assertTrue($result);
        $this->assertModelMissing($record);
    }
}
