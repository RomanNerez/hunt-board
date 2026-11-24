<?php

namespace App\Containers\AppSection\Resume\Data\Factories;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ResumeLink
 *
 * @extends ParentFactory<TModel>
 */
final class ResumeLinkFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ResumeLink::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'icon' => null,
            'label' => fake()->word(),
            'url' => fake()->url(),
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }
}
