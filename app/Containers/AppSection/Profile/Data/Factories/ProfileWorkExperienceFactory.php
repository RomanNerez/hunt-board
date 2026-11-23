<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ProfileWorkExperience
 *
 * @extends ParentFactory<TModel>
 */
final class ProfileWorkExperienceFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ProfileWorkExperience::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedOn = fake()->dateTimeBetween('-10 years', '-2 years');

        return [
            'user_id' => User::factory(),
            'type' => WorkExperienceType::COMPANY,
            'company_name' => fake()->company(),
            'role' => fake()->jobTitle(),
            'started_on' => $startedOn->format('Y-m-d'),
            'finished_on' => fake()->dateTimeBetween($startedOn, '-1 month')->format('Y-m-d'),
        ];
    }

    /**
     * @return static
     */
    public function freelance(): static
    {
        return $this->state(['type' => WorkExperienceType::FREELANCE]);
    }

    /**
     * @return static
     */
    public function current(): static
    {
        return $this->state(['finished_on' => null]);
    }
}
