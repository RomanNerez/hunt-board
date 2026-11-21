<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Company\Data\Factories\CompanyFactory;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CompanyFactory::class)]
final class CompanyFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesCompanyWithOnlyName(): void
    {
        $company = Company::factory()->createOne()->load(['companyType', 'domains']);

        $this->assertNotSame('', $company->name);
        $this->assertNull($company->companyType);
        $this->assertCount(0, $company->domains);
    }

    /**
     * @return void
     */
    public function testCreatesCompanyWithTypeAndDomains(): void
    {
        $company = Company::factory()->withType()->withDomains(3)->createOne()->load(['companyType', 'domains']);

        $this->assertNotNull($company->companyType);
        $this->assertCount(3, $company->domains);
    }
}
