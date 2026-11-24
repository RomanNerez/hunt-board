<?php

namespace App\Containers\AppSection\Resume\Data\Factories;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ResumeLanguage
 *
 * @extends ParentFactory<TModel>
 */
final class ResumeLanguageFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ResumeLanguage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'language' => fake()->unique()->word(),
            'level' => null,
            'level_label' => null,
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
