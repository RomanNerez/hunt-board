<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\UpdateCompanyTypeAction;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateCompanyTypeAction::class)]
final class UpdateCompanyTypeActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesCompanyTypeName(): void
    {
        $record = CompanyType::factory()->createOne(['name' => 'Аутстаф']);

        $result = app(UpdateCompanyTypeAction::class)->run($record->id, ['name' => 'Аутсорс']);

        $this->assertSame('Аутсорс', $result->name);
        $this->assertDatabaseHas(CompanyType::class, ['id' => $record->id, 'name' => 'Аутсорс']);
    }
}
