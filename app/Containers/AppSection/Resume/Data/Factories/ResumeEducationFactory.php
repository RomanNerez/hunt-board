<?php

namespace App\Containers\AppSection\Resume\Data\Factories;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ResumeEducation
 *
 * @extends ParentFactory<TModel>
 */
final class ResumeEducationFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ResumeEducation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'institution' => fake()->company(),
            'institution_url' => null,
            'degree' => null,
            'field_of_study' => null,
            'location' => null,
            'start_date' => null,
            'end_date' => null,
            'description' => null,
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
