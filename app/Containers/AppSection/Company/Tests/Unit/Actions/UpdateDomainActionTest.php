<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\UpdateDomainAction;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateDomainAction::class)]
final class UpdateDomainActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesDomainName(): void
    {
        $record = Domain::factory()->createOne(['name' => 'Fintech']);

        $result = app(UpdateDomainAction::class)->run($record->id, ['name' => 'Gamedev']);

        $this->assertSame('Gamedev', $result->name);
        $this->assertDatabaseHas(Domain::class, ['id' => $record->id, 'name' => 'Gamedev']);
    }
}
