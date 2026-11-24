<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Support\SalaryRangeFormatter;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

final class VacancyInfolist
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
                Section::make('Основне')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Назва'),
                        TextEntry::make('company.name')
                            ->label('Компанія'),
                        TextEntry::make('status.name')
                            ->label('Статус')
                            ->formatStateUsing(static fn (Vacancy $record): HtmlString => $record->status->color->renderBadge($record->status->name)),
                        TextEntry::make('vacancySource.name')
                            ->label('Джерело')
                            ->placeholder('—'),
                        TextEntry::make('url')
                            ->label('Посилання')
                            ->url(static fn (Vacancy $record): string|null => $record->url, shouldOpenInNewTab: true)
                            ->placeholder('—'),
                        TextEntry::make('location')
                            ->label('Локація')
                            ->placeholder('—'),
                        TextEntry::make('work_format')
                            ->label('Формат роботи')
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Умови')
                    ->schema([
                        TextEntry::make('salary')
                            ->label('Зарплата')
                            ->state(static fn (Vacancy $record): string|null => SalaryRangeFormatter::format($record->salary_min, $record->salary_max, $record->currency))
                            ->placeholder('—'),
                    ])
                    ->columnSpanFull(),
                Section::make('Дати')
                    ->schema([
                        TextEntry::make('applied_at')
                            ->label('Дата відгуку')
                            ->date('d.m.Y')
                            ->placeholder('—'),
                        TextEntry::make('next_action_at')
                            ->label('Наступна дія')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Опис')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Опис')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->placeholder('—'),
                    ])
                    ->columnSpanFull(),
                Section::make('Нотатки')
                    ->schema([
                        self::multilineEntry('notes', 'Нотатки'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @param string $name
     * @param string $label
     *
     * @return TextEntry
     */
    private static function multilineEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->hiddenLabel()
            ->extraAttributes(['class' => 'whitespace-pre-line'])
            ->placeholder('—');
    }
}
