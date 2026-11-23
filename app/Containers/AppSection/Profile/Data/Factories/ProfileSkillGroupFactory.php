<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ProfileSkillGroup
 *
 * @extends ParentFactory<TModel>
 */
final class ProfileSkillGroupFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ProfileSkillGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Backend', 'Frontend', 'Бази даних', 'DevOps', 'Інструменти']),
            'sort' => 0,
        ];
    }

    /**
     * @param int $count
     *
     * @return static
     */
    public function withStacks(int $count = 3): static
    {
        return $this->afterCreating(static function (ProfileSkillGroup $record) use ($count): void {
            foreach (Stack::factory()->count($count)->create()->values() as $sort => $stack) {
                $record->stacks()->attach($stack->id, ['sort' => $sort]);
            }
        });
    }
}
