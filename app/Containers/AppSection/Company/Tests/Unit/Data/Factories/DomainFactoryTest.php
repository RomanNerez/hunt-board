<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Company\Data\Factories\DomainFactory;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DomainFactory::class)]
final class DomainFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesDomainsWithDistinctNames(): void
    {
        $records = Domain::factory()->count(3)->create();

        $this->assertDatabaseCount(Domain::class, 3);
        $this->assertCount(3, $records->pluck('name')->unique());
    }
}
