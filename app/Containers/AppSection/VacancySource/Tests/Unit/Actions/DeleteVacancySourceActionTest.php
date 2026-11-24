<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Actions;

use App\Containers\AppSection\VacancySource\Actions\DeleteVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteVacancySourceAction::class)]
final class DeleteVacancySourceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesVacancySource(): void
    {
        $record = VacancySource::factory()->createOne();
        $other = VacancySource::factory()->createOne();

        $result = app(DeleteVacancySourceAction::class)->run($record->id);

        $this->assertTrue($result);
        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }
}
