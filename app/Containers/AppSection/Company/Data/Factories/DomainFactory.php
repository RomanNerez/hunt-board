<?php

namespace App\Containers\AppSection\Company\Data\Factories;

use App\Containers\AppSection\Company\Models\Domain;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Domain
 *
 * @extends ParentFactory<TModel>
 */
final class DomainFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Domain::class;

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
