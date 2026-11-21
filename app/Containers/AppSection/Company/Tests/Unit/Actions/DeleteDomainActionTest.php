<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\DeleteDomainAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteDomainAction::class)]
final class DeleteDomainActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesDomain(): void
    {
        $record = Domain::factory()->createOne();
        $other = Domain::factory()->createOne();

        $result = app(DeleteDomainAction::class)->run($record->id);

        $this->assertTrue($result);
        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }

    /**
     * @return void
     */
    public function testRemovesOnlyDeletedDomainFromCompanies(): void
    {
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $cybersecurity = Domain::factory()->createOne(['name' => 'Cybersecurity']);
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $macpaw->domains()->attach([$utilities->id, $cybersecurity->id]);

        app(DeleteDomainAction::class)->run($cybersecurity->id);

        $this->assertModelExists($macpaw);
        $this->assertSame(['Utilities'], $macpaw->domains()->pluck('name')->all());
    }
}
