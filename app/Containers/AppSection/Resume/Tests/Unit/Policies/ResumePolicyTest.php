<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Policies;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Policies\ResumePolicy;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ResumePolicy::class)]
final class ResumePolicyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDiscoveredForResumeModel(): void
    {
        $this->assertInstanceOf(ResumePolicy::class, Gate::getPolicyFor(Resume::class));
    }

    /**
     * @return void
     */
    public function testAllowsAnyUserToListAndCreate(): void
    {
        $user = User::factory()->createOne();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Resume::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Resume::class));
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
    public function testAllowsOwnerOnly(string $ability): void
    {
        $owner = User::factory()->createOne();
        $resume = Resume::factory()->createOne(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows($ability, $resume));
        $this->assertFalse(Gate::forUser(User::factory()->createOne())->allows($ability, $resume));
    }
}
