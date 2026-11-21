<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class DomainForm
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
