<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Policies;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Policies\StackPolicy;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(StackPolicy::class)]
final class StackPolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForStackModel(): void
    {
        $this->assertInstanceOf(StackPolicy::class, Gate::getPolicyFor(Stack::class));
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
        $argument = $needsRecord ? Stack::factory()->createOne() : Stack::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
