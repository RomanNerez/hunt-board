<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Policies;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Policies\CompanyTypePolicy;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CompanyTypePolicy::class)]
final class CompanyTypePolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForCompanyTypeModel(): void
    {
        $this->assertInstanceOf(CompanyTypePolicy::class, Gate::getPolicyFor(CompanyType::class));
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
        $argument = $needsRecord ? CompanyType::factory()->createOne() : CompanyType::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
