<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Policies;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Policies\StatusPolicy;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(StatusPolicy::class)]
final class StatusPolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForStatusModel(): void
    {
        $this->assertInstanceOf(StatusPolicy::class, Gate::getPolicyFor(Status::class));
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
        $argument = $needsRecord ? Status::factory()->createOne() : Status::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
