<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ResumeForm
{
    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('first_name')
                    ->label('Імʼя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('Прізвище')
                    ->required()
                    ->maxLength(255),
                TextInput::make('headline')
                    ->label('Заголовок')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('summary')
                    ->label('Короткий опис')
                    ->rows(6)
                    ->columnSpanFull(),
                ResumeLinksRepeater::make(),
                ResumeExperiencesRepeater::make(),
                ResumeEducationsRepeater::make(),
                ResumeLanguagesRepeater::make(),
            ]);
    }
}
