<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Policies;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Policies\ProfileProjectPolicy;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ProfileProjectPolicy::class)]
final class ProfileProjectPolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForProjectModel(): void
    {
        $this->assertInstanceOf(ProfileProjectPolicy::class, Gate::getPolicyFor(ProfileProject::class));
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
    public function testWorkExperienceOwnerIsAllowed(string $ability): void
    {
        $record = ProfileProject::factory()->createOne();
        $owner = User::query()->findOrFail(ProfileWorkExperience::query()->findOrFail($record->work_experience_id)->user_id);

        $this->assertTrue(Gate::forUser($owner)->allows($ability, $record));
    }

    /**
     * @param string $ability
     *
     * @return void
     */
    #[DataProvider('recordAbilityProvider')]
    public function testOtherUserIsDenied(string $ability): void
    {
        $record = ProfileProject::factory()->createOne();

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
        $this->assertTrue(Gate::forUser(User::factory()->createOne())->allows($ability, ProfileProject::class));
    }
}
