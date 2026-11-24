<?php

namespace App\Containers\AppSection\Contact\Data\Factories;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Contact
 *
 * @extends ParentFactory<TModel>
 */
final class ContactFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }
}
