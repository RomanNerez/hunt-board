<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Models;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Concerns\HasStacks;
use App\Containers\AppSection\Stack\Models\Contracts\Stackable;
use App\Containers\AppSection\Stack\Models\Stack;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversTrait(HasStacks::class)]
final class ProfileStacksTest extends UnitTestCase
{
    /**
     * @return list<array{string}>
     */
    public static function ownerProvider(): array
    {
        return [
            ['profile_skill_group'],
            ['profile_project'],
        ];
    }

    /**
     * @param string $morphType
     *
     * @return void
     */
    #[DataProvider('ownerProvider')]
    public function testStacksAreOrderedBySortAndStoredUnderMorphAlias(string $morphType): void
    {
        $owner = $this->owner($morphType);
        [$php, $laravel, $node] = Stack::factory()->count(3)->create()->all();
        $owner->stacks()->attach([
            $php->id => ['sort' => 2],
            $laravel->id => ['sort' => 0],
            $node->id => ['sort' => 1],
        ]);

        $this->assertSame([$laravel->id, $node->id, $php->id], $owner->stacks()->pluck('stacks.id')->all());
        $this->assertDatabaseHas('stackables', [
            'stack_id' => $php->id,
            'stackable_type' => $morphType,
            'stackable_id' => $owner->getKey(),
        ]);
    }

    /**
     * @param string $morphType
     *
     * @return Model&Stackable
     */
    private function owner(string $morphType): Model&Stackable
    {
        return 'profile_skill_group' === $morphType
            ? ProfileSkillGroup::factory()->createOne()
            : ProfileProject::factory()->createOne();
    }
}
