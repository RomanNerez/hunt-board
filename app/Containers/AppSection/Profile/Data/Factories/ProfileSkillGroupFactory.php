<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
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
            'skills' => fake()->randomElements(['PHP', 'Laravel', 'Vue.js', 'React', 'PostgreSQL', 'Redis', 'Docker', 'Git'], fake()->numberBetween(2, 4)),
            'sort' => 0,
        ];
    }
}
