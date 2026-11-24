<?php

namespace App\Containers\AppSection\Vacancy\Data\Factories;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of VacancyNote
 *
 * @extends ParentFactory<TModel>
 */
final class VacancyNoteFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = VacancyNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'title' => fake()->sentence(4),
            'body' => '<p>' . fake()->paragraph() . '</p>',
            'occurred_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
