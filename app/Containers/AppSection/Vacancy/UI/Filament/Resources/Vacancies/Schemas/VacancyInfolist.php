<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Support\SalaryRangeFormatter;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Support\VacancySourceLink;
use Filament\Infolists\Components\RepeatableEntry;
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
                        TextEntry::make('location')
                            ->label('Локація')
                            ->placeholder('—'),
                        TextEntry::make('work_format')
                            ->label('Формат роботи')
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Джерела')
                    ->schema([
                        self::sourcesEntry(),
                    ])
                    ->columnSpanFull(),
                Section::make('Контакти')
                    ->schema([
                        self::contactsEntry(),
                    ])
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
            ]);
    }

    /**
     * @return RepeatableEntry
     */
    private static function sourcesEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('vacancySources')
            ->label('Джерела')
            ->hiddenLabel()
            ->schema([
                TextEntry::make('name')
                    ->label('Джерело'),
                TextEntry::make('pivot.url')
                    ->label('Посилання')
                    ->url(static fn (VacancySource $record): string|null => VacancySourceLink::url($record), shouldOpenInNewTab: true),
            ])
            ->columns(2)
            ->placeholder('—')
            ->columnSpanFull();
    }

    /**
     * @return RepeatableEntry
     */
    private static function contactsEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('contacts')
            ->label('Контакти')
            ->hiddenLabel()
            ->schema([
                TextEntry::make('name')
                    ->label('Ім\'я'),
                RepeatableEntry::make('vacancySources')
                    ->label('Джерела')
                    ->hiddenLabel()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Джерело'),
                        TextEntry::make('pivot.url')
                            ->label('Посилання')
                            ->url(static fn (VacancySource $record): string|null => VacancySourceLink::url($record), shouldOpenInNewTab: true),
                    ])
                    ->columns(2)
                    ->contained(false),
            ])
            ->placeholder('—')
            ->columnSpanFull();
    }
}
