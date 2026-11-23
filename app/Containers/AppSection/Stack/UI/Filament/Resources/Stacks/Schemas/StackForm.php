<?php

namespace App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Schemas;

use App\Containers\AppSection\Stack\Models\Stack;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class StackForm
{
    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::nameInput(),
            ])
            ->columns(1);
    }

    /**
     * @return TextInput
     */
    public static function nameInput(): TextInput
    {
        return TextInput::make('name')
            ->label('Назва')
            ->required()
            ->maxLength(Stack::NAME_MAX_LENGTH)
            ->rule(static fn (mixed $record): \Closure => static function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                $ignoredId = $record instanceof Stack ? $record->id : null;

                if (is_string($value) && Stack::nameIsTaken($value, $ignoredId)) {
                    $fail(__('validation.unique', ['attribute' => 'назва']));
                }
            });
    }
}
