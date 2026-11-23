<?php

namespace App\Containers\AppSection\Profile\Data\Factories;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of ProfileProject
 *
 * @extends ParentFactory<TModel>
 */
final class ProfileProjectFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = ProfileProject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedOn = fake()->dateTimeBetween('-10 years', '-2 years');

        return [
            'work_experience_id' => ProfileWorkExperience::factory(),
            'name' => fake()->randomElement(['Особистий кабінет клієнта', 'Промо-лендинг', 'Аукціон з продажу автомобілів', 'CRM для відділу продажів']),
            'started_on' => $startedOn->format('Y-m-d'),
            'finished_on' => fake()->dateTimeBetween($startedOn, '-1 month')->format('Y-m-d'),
        ];
    }

    /**
     * @return static
     */
    public function current(): static
    {
        return $this->state(['finished_on' => null]);
    }

    /**
     * @return static
     */
    public function withDescription(): static
    {
        return $this->state([
            'description' => [
                'uk' => '<p>Міграція бази даних з MySQL на PostgreSQL.</p><ul><li><p>Мігровано понад 10 млн записів</p></li></ul>',
                'en' => '<p>Database migration from MySQL to PostgreSQL.</p><ul><li><p>Migrated over 10M records</p></li></ul>',
            ],
        ]);
    }

    /**
     * @param int $count
     *
     * @return static
     */
    public function withStacks(int $count = 3): static
    {
        return $this->afterCreating(static function (ProfileProject $record) use ($count): void {
            foreach (Stack::factory()->count($count)->create()->values() as $sort => $stack) {
                $record->stacks()->attach($stack->id, ['sort' => $sort]);
            }
        });
    }
}
