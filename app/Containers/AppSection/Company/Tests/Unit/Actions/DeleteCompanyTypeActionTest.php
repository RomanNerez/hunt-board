<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\DeleteCompanyTypeAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteCompanyTypeAction::class)]
final class DeleteCompanyTypeActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesCompanyType(): void
    {
        $record = CompanyType::factory()->createOne();
        $other = CompanyType::factory()->createOne();

        $result = app(DeleteCompanyTypeAction::class)->run($record->id);

        $this->assertTrue($result);
        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }

    /**
     * @return void
     */
    public function testKeepsCompaniesOfDeletedTypeWithoutType(): void
    {
        $outsource = CompanyType::factory()->createOne(['name' => 'Аутсорс']);
        $epam = Company::factory()->createOne(['name' => 'EPAM', 'company_type_id' => $outsource->id]);

        app(DeleteCompanyTypeAction::class)->run($outsource->id);

        $this->assertModelMissing($outsource);
        $this->assertDatabaseHas(Company::class, ['id' => $epam->id, 'company_type_id' => null]);
    }
}
