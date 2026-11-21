<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Policies;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Policies\CompanyPolicy;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CompanyPolicy::class)]
final class CompanyPolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForCompanyModel(): void
    {
        $this->assertInstanceOf(CompanyPolicy::class, Gate::getPolicyFor(Company::class));
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
        $argument = $needsRecord ? Company::factory()->createOne() : Company::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
