<?php

namespace App\Containers\AppSection\Resume\Data\Factories;

use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ResumeExperienceProject
 *
 * @extends ParentFactory<TModel>
 */
final class ResumeExperienceProjectFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ResumeExperienceProject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_experience_id' => ResumeExperience::factory(),
            'name' => fake()->words(2, true),
            'role' => null,
            'description' => null,
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
