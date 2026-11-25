<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
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
                self::firstNameInput(),
                self::lastNameInput(),
                self::localeField()
                    ->columnSpanFull(),
                TextInput::make('headline')
                    ->label('Заголовок')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('summary')
                    ->label('Короткий опис')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return TextInput
     */
    public static function firstNameInput(): TextInput
    {
        return TextInput::make('first_name')
            ->label('Імʼя')
            ->required()
            ->maxLength(255);
    }

    /**
     * @return TextInput
     */
    public static function lastNameInput(): TextInput
    {
        return TextInput::make('last_name')
            ->label('Прізвище')
            ->required()
            ->maxLength(255);
    }

    /**
     * @return ToggleButtons
     */
    public static function localeField(): ToggleButtons
    {
        return ToggleButtons::make('locale')
            ->label('Мова резюме')
            ->options(ResumeLocale::class)
            ->default(ResumeLocale::default())
            ->required()
            ->inline();
    }
}
