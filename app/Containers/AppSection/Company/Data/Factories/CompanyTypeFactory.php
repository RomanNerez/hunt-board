<?php

namespace App\Containers\AppSection\Company\Data\Factories;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of CompanyType
 *
 * @extends ParentFactory<TModel>
 */
final class CompanyTypeFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = CompanyType::class;

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
