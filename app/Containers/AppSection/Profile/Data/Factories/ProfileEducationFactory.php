<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ProfileEducation
 *
 * @extends ParentFactory<TModel>
 */
final class ProfileEducationFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ProfileEducation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(EducationType::cases()),
            'title' => fake()->sentence(3),
            'institution' => fake()->company(),
            'sort' => 0,
        ];
    }
}
