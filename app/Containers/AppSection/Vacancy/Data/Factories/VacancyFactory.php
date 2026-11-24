<?php

namespace App\Containers\AppSection\Vacancy\Data\Factories;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Vacancy
 *
 * @extends ParentFactory<TModel>
 */
final class VacancyFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Vacancy::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'status_id' => Status::factory(),
            'title' => fake()->jobTitle(),
        ];
    }

    /**
     * @return static
     */
    public function complete(): static
    {
        return $this->state(function (): array {
            $salaryMin = fake()->numberBetween(1, 5) * 1000;

            return [
                'description' => fake()->paragraphs(2, true),
                'location' => fake()->city(),
                'work_format' => fake()->randomElement(WorkFormat::cases()),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMin + 1500,
                'currency' => fake()->randomElement(Currency::cases()),
                'applied_at' => fake()->date(),
                'next_action_at' => fake()->dateTimeBetween('now', '+1 month'),
            ];
        })->hasAttached(VacancySource::factory()->count(2), static fn (): array => ['url' => fake()->url()]);
    }
}
