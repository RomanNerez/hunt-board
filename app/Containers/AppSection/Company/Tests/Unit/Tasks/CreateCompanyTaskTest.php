<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\CreateCompanyTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateCompanyTask::class)]
final class CreateCompanyTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesCompany(): void
    {
        $company = app(CreateCompanyTask::class)->run([
            'name' => 'MacPaw',
            'website_url' => 'https://macpaw.com',
        ]);

        $this->assertSame('MacPaw', $company->name);
        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'name' => 'MacPaw',
            'website_url' => 'https://macpaw.com',
            'description' => null,
        ]);
    }
}
