<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Actions;

use App\Containers\AppSection\VacancySource\Actions\CreateVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateVacancySourceAction::class)]
final class CreateVacancySourceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesVacancySourceWithName(): void
    {
        $record = app(CreateVacancySourceAction::class)->run(['name' => 'LinkedIn']);

        $this->assertSame('LinkedIn', $record->name);
        $this->assertDatabaseHas(VacancySource::class, ['id' => $record->id, 'name' => 'LinkedIn']);
    }
}
