<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\UpdateCompanyTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateCompanyTask::class)]
final class UpdateCompanyTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesCompanyAttributes(): void
    {
        $company = Company::factory()->createOne(['name' => 'MacPaw']);

        $result = app(UpdateCompanyTask::class)->run($company->id, ['description' => 'Утиліти для macOS']);

        $this->assertSame($company->id, $result->id);
        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'name' => 'MacPaw',
            'description' => 'Утиліти для macOS',
        ]);
    }
}
