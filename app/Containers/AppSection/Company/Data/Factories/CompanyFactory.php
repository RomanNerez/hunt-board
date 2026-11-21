<?php

namespace App\Containers\AppSection\Company\Data\Factories;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Models\Domain;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Company
 *
 * @extends ParentFactory<TModel>
 */
final class CompanyFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
        ];
    }

    /**
     * @return static
     */
    public function withType(): static
    {
        return $this->for(CompanyType::factory(), 'companyType');
    }

    /**
     * @param int $count
     *
     * @return static
     */
    public function withDomains(int $count = 2): static
    {
        return $this->hasAttached(Domain::factory()->count($count), [], 'domains');
    }
}
