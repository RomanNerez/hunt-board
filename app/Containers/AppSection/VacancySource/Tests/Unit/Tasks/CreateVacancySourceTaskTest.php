<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Tasks;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tasks\CreateVacancySourceTask;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateVacancySourceTask::class)]
final class CreateVacancySourceTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesVacancySource(): void
    {
        $record = app(CreateVacancySourceTask::class)->run(['name' => 'Djinni']);

        $this->assertSame('Djinni', $record->name);
        $this->assertDatabaseHas(VacancySource::class, ['id' => $record->id, 'name' => 'Djinni']);
    }
}
