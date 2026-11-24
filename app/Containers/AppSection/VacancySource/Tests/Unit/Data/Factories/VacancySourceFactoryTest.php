<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Data\Factories;

use App\Containers\AppSection\VacancySource\Data\Factories\VacancySourceFactory;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VacancySourceFactory::class)]
final class VacancySourceFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesVacancySourcesWithDistinctNames(): void
    {
        $records = VacancySource::factory()->count(3)->create();

        $this->assertDatabaseCount(VacancySource::class, 3);
        $this->assertCount(3, $records->pluck('name')->unique());
    }
}
