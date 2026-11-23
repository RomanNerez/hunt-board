<?php

namespace App\Containers\AppSection\Stack\Data\Factories;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Stack
 *
 * @extends ParentFactory<TModel>
 */
final class StackFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Stack::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Tech-####-??'),
        ];
    }
}
