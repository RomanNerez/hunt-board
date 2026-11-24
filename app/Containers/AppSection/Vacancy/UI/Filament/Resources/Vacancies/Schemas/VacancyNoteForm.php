<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class VacancyNoteForm
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
                TextInput::make('title')
                    ->label('Заголовок')
                    ->placeholder('Напр. Результат співбесіди з HR')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DateTimePicker::make('occurred_at')
                    ->label('Коли сталося')
                    ->seconds(false)
                    ->default(static fn (): string => now()->format('Y-m-d H:i'))
                    ->required(),
                self::bodyEditor(),
            ])
            ->columns(1);
    }

    /**
     * @return RichEditor
     */
    private static function bodyEditor(): RichEditor
    {
        return RichEditor::make('body')
            ->label('Текст')
            ->required()
            ->toolbarButtons([
                ['bold', 'italic', 'link'],
                ['h2', 'h3'],
                ['bulletList', 'orderedList', 'blockquote'],
                ['undo', 'redo'],
            ])
            ->fileAttachments(false)
            ->columnSpanFull();
    }
}
