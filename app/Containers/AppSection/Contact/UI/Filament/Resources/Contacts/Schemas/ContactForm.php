<?php

namespace App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Schemas;

use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas\VacancySourcesRepeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ContactForm
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
                    ->label('Ім\'я')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                VacancySourcesRepeater::make(),
            ])
            ->columns(1);
    }
}
