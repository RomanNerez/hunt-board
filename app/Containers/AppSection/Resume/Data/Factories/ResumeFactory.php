<?php

namespace App\Containers\AppSection\Resume\Data\Factories;

use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Resume
 *
 * @extends ParentFactory<TModel>
 */
final class ResumeFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Resume::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kind' => ResumeKind::Master,
            'locale' => ResumeLocale::English,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'headline' => fake()->jobTitle(),
            'summary' => fake()->paragraph(),
        ];
    }

    /**
     * @return static
     */
    public function master(): static
    {
        return $this->state(['kind' => ResumeKind::Master]);
    }

    /**
     * @return static
     */
    public function application(): static
    {
        return $this->state(['kind' => ResumeKind::Application]);
    }

    /**
     * @return static
     */
    public function ukrainian(): static
    {
        return $this->state(['locale' => ResumeLocale::Ukrainian]);
    }
}
