<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ProfileLanguage
 *
 * @extends ParentFactory<TModel>
 */
final class ProfileLanguageFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ProfileLanguage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'language' => fake()->unique()->languageCode(),
            'level' => fake()->randomElement(LanguageLevel::cases()),
            'sort' => 0,
        ];
    }
}
