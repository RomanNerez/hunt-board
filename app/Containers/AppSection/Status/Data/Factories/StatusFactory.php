<?php

namespace App\Containers\AppSection\Status\Data\Factories;

use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Status
 *
 * @extends ParentFactory<TModel>
 */
final class StatusFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Status::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->randomElement(StatusColor::cases()),
        ];
    }
}
