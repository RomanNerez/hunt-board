<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Vacancy\Data\Factories\VacancyFactory;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VacancyFactory::class)]
final class VacancyFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesVacancyWithOnlyRequiredAttributes(): void
    {
        $vacancy = Vacancy::factory()->createOne()->refresh();

        $this->assertNotSame('', $vacancy->title);
        $this->assertNotNull($vacancy->company);
        $this->assertNotNull($vacancy->status);
        $this->assertNull($vacancy->salary_min);
        $this->assertNull($vacancy->currency);
    }

    /**
     * @return void
     */
    public function testCompleteStateFillsEveryAttribute(): void
    {
        $vacancy = Vacancy::factory()->complete()->createOne()->refresh();

        foreach ($vacancy->getFillable() as $attribute) {
            $this->assertNotNull($vacancy->getAttribute($attribute), $attribute);
        }

        $this->assertGreaterThanOrEqual($vacancy->salary_min, $vacancy->salary_max);
    }
}
