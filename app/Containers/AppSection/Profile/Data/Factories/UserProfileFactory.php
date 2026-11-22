<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of UserProfile
 *
 * @extends ParentFactory<TModel>
 */
final class UserProfileFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = UserProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'headline' => fake()->jobTitle(),
        ];
    }
}
