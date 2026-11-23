<?php

namespace App\Containers\AppSection\Stack\UI\Filament\Components;

use App\Containers\AppSection\Stack\Actions\CreateStackAction;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Schemas\StackForm;
use Filament\Forms\Components\Select;

final class StackSelect
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return Select
     */
    public static function make(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->multiple()
            ->searchable()
            ->preload()
            ->options(static fn (): array => Stack::query()->orderBy('name')->pluck('name', 'id')->all())
            ->createOptionForm([StackForm::nameInput()])
            ->createOptionModalHeading('Створити технологію')
            ->createOptionUsing(static function (array $data): int {
                /** @var array<string, mixed> $attributes */
                $attributes = $data;

                return app(CreateStackAction::class)->run($attributes)->id;
            });
    }
}
