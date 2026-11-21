<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tasks\SyncCompanyDomainsTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncCompanyDomainsTask::class)]
final class SyncCompanyDomainsTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testReplacesCompanyDomainsWithGivenOnes(): void
    {
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $cybersecurity = Domain::factory()->createOne(['name' => 'Cybersecurity']);
        $edtech = Domain::factory()->createOne(['name' => 'EdTech']);
        $company = Company::factory()->createOne();
        $company->domains()->attach([$utilities->id, $cybersecurity->id]);

        app(SyncCompanyDomainsTask::class)->run($company, [$utilities->id, $edtech->id]);

        $this->assertEqualsCanonicalizing(['Utilities', 'EdTech'], $company->domains()->pluck('name')->all());
        $this->assertModelExists($cybersecurity);
    }

    /**
     * @return void
     */
    public function testEmptyListDetachesAllDomains(): void
    {
        $company = Company::factory()->withDomains(2)->createOne();

        app(SyncCompanyDomainsTask::class)->run($company, []);

        $this->assertSame(0, $company->domains()->count());
    }
}
