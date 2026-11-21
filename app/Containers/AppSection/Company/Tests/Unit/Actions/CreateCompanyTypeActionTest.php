<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\CreateCompanyTypeAction;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateCompanyTypeAction::class)]
final class CreateCompanyTypeActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesCompanyTypeWithName(): void
    {
        $record = app(CreateCompanyTypeAction::class)->run(['name' => 'Аутстаф']);

        $this->assertSame('Аутстаф', $record->name);
        $this->assertDatabaseHas(CompanyType::class, ['id' => $record->id, 'name' => 'Аутстаф']);
    }
}
