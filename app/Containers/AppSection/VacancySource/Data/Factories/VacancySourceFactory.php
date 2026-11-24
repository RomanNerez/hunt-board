<?php

namespace App\Containers\AppSection\VacancySource\Data\Factories;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of VacancySource
 *
 * @extends ParentFactory<TModel>
 */
final class VacancySourceFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = VacancySource::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
