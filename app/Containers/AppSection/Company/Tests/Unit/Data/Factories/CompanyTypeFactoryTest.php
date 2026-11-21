<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Company\Data\Factories\CompanyTypeFactory;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CompanyTypeFactory::class)]
final class CompanyTypeFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesCompanyTypesWithDistinctNames(): void
    {
        $records = CompanyType::factory()->count(3)->create();

        $this->assertDatabaseCount(CompanyType::class, 3);
        $this->assertCount(3, $records->pluck('name')->unique());
    }
}
