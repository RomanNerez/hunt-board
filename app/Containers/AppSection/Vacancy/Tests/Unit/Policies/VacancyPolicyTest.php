<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Policies\VacancyPolicy;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(VacancyPolicy::class)]
final class VacancyPolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForVacancyModel(): void
    {
        $this->assertInstanceOf(VacancyPolicy::class, Gate::getPolicyFor(Vacancy::class));
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
        $argument = $needsRecord ? Vacancy::factory()->createOne() : Vacancy::class;

        $this->assertTrue(Gate::forUser($user)->allows($ability, $argument));
    }
}
