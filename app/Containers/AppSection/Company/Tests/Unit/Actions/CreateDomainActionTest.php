<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\CreateDomainAction;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateDomainAction::class)]
final class CreateDomainActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesDomainWithName(): void
    {
        $record = app(CreateDomainAction::class)->run(['name' => 'Fintech']);

        $this->assertSame('Fintech', $record->name);
        $this->assertDatabaseHas(Domain::class, ['id' => $record->id, 'name' => 'Fintech']);
    }
}
