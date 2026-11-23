<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Policies;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Policies\ProfileWorkExperiencePolicy;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ProfileWorkExperiencePolicy::class)]
final class ProfileWorkExperiencePolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForWorkExperienceModel(): void
    {
        $this->assertInstanceOf(ProfileWorkExperiencePolicy::class, Gate::getPolicyFor(ProfileWorkExperience::class));
    }

    /**
     * @return list<array{string}>
     */
    public static function recordAbilityProvider(): array
    {
        return [['view'], ['update'], ['delete']];
    }

    /**
     * @param string $ability
     *
     * @return void
     */
    #[DataProvider('recordAbilityProvider')]
    public function testOwnerIsAllowed(string $ability): void
    {
        $record = ProfileWorkExperience::factory()->createOne();

        $this->assertTrue(Gate::forUser($record->user)->allows($ability, $record));
    }

    /**
     * @param string $ability
     *
     * @return void
     */
    #[DataProvider('recordAbilityProvider')]
    public function testOtherUserIsDenied(string $ability): void
    {
        $record = ProfileWorkExperience::factory()->createOne();

        $this->assertTrue(Gate::forUser(User::factory()->createOne())->denies($ability, $record));
    }

    /**
     * @return list<array{string}>
     */
    public static function modelAbilityProvider(): array
    {
        return [['viewAny'], ['create'], ['deleteAny']];
    }

    /**
     * @param string $ability
     *
     * @return void
     */
    #[DataProvider('modelAbilityProvider')]
    public function testAnyUserIsAllowedModelAbilities(string $ability): void
    {
        $this->assertTrue(Gate::forUser(User::factory()->createOne())->allows($ability, ProfileWorkExperience::class));
    }
}
