<?php

namespace App\Containers\AppSection\Resume\Data\Factories;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ResumeExperience
 *
 * @extends ParentFactory<TModel>
 */
final class ResumeExperienceFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ResumeExperience::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'company' => fake()->company(),
            'company_url' => null,
            'position' => fake()->jobTitle(),
            'location' => null,
            'employment_type' => null,
            'start_date' => '2021-03-01',
            'end_date' => null,
            'description' => null,
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
