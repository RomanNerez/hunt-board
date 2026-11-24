<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Actions;

use App\Containers\AppSection\VacancySource\Actions\UpdateVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateVacancySourceAction::class)]
final class UpdateVacancySourceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesVacancySourceName(): void
    {
        $record = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        $result = app(UpdateVacancySourceAction::class)->run($record->id, ['name' => 'Djinni']);

        $this->assertSame('Djinni', $result->name);
        $this->assertDatabaseHas(VacancySource::class, ['id' => $record->id, 'name' => 'Djinni']);
    }
}
