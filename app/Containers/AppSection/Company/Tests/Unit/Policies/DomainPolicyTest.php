<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Policies;

use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Policies\DomainPolicy;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DomainPolicy::class)]
final class DomainPolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForDomainModel(): void
    {
        $this->assertInstanceOf(DomainPolicy::class, Gate::getPolicyFor(Domain::class));
    }

    /**
     * @return list<array{string, bool}>
     */
    public static function abilityProvider(): array
    {
        return [
            ['viewAny', false],
            ['view', true],
            ['create', false],
            ['update', true],
            ['delete', true],
            ['deleteAny', false],
        ];
    }

    /**
     * @param string $ability
     * @param bool $needsRecord
     *
     * @return void
     */
    #[DataProvider('abilityProvider')]
    public function testAllowsAuthenticatedUser(string $ability, bool $needsRecord): void
    {
        $user = User::factory()->createOne();
        $argument = $needsRecord ? Domain::factory()->createOne() : Domain::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
