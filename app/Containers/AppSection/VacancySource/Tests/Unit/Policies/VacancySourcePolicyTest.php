<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Policies\VacancySourcePolicy;
use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(VacancySourcePolicy::class)]
final class VacancySourcePolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForVacancySourceModel(): void
    {
        $this->assertInstanceOf(VacancySourcePolicy::class, Gate::getPolicyFor(VacancySource::class));
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
        $argument = $needsRecord ? VacancySource::factory()->createOne() : VacancySource::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
