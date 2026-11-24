<?php

namespace App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class VacancySourceForm
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
                TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ])
            ->columns(1);
    }
}
