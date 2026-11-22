<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ProfileLink
 *
 * @extends ParentFactory<TModel>
 */
final class ProfileLinkFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ProfileLink::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['LinkedIn', 'GitHub', 'Portfolio', 'Telegram']),
            'url' => fake()->url(),
            'sort' => 0,
        ];
    }
}
