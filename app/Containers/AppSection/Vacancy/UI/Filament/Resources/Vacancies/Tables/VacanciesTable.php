<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Tables;

use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use App\Containers\AppSection\Vacancy\UI\Filament\Support\SalaryRangeFormatter;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

final class VacanciesTable
{
    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Компанія')
                    ->searchable(),
                TextColumn::make('status.name')
                    ->label('Статус')
                    ->formatStateUsing(static fn (Vacancy $record): HtmlString => $record->status->color->renderBadge($record->status->name)),
                TextColumn::make('vacancySource.name')
                    ->label('Джерело')
                    ->placeholder('—'),
                TextColumn::make('work_format')
                    ->label('Формат')
                    ->placeholder('—'),
                TextColumn::make('salary')
                    ->label('Зарплата')
                    ->state(static fn (Vacancy $record): string|null => SalaryRangeFormatter::format($record->salary_min, $record->salary_max, $record->currency))
                    ->placeholder('—'),
                TextColumn::make('applied_at')
                    ->label('Дата відгуку')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('next_action_at')
                    ->label('Наступна дія')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->relationship('status', 'name')
                    ->preload(),
                SelectFilter::make('company')
                    ->label('Компанія')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('vacancySource')
                    ->label('Джерело')
                    ->relationship('vacancySource', 'name')
                    ->preload(),
                SelectFilter::make('work_format')
                    ->label('Формат роботи')
                    ->options(WorkFormat::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordUrl(static fn (Vacancy $record): string => VacancyResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([]);
    }
}
