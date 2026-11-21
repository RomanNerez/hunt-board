<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\DeleteCompanyTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteCompanyTask::class)]
final class DeleteCompanyTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesCompany(): void
    {
        $company = Company::factory()->createOne();
        $other = Company::factory()->createOne();

        $result = app(DeleteCompanyTask::class)->run($company->id);

        $this->assertTrue($result);
        $this->assertModelMissing($company);
        $this->assertModelExists($other);
    }
}
