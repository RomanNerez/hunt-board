<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Tables;

use App\Containers\AppSection\Vacancy\Actions\DeleteVacancyNoteAction;
use App\Containers\AppSection\Vacancy\Actions\UpdateVacancyNoteAction;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class VacancyNotesTable
{
    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(static fn (Builder $query): Builder => self::newestFirst($query))
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('title')
                            ->label('Заголовок')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large),
                        TextColumn::make('occurred_at')
                            ->label('Коли сталося')
                            ->dateTime('d.m.Y H:i')
                            ->icon(Heroicon::OutlinedCalendar)
                            ->color('gray')
                            ->alignEnd()
                            ->grow(false),
                    ])->from('sm'),
                    TextColumn::make('body')
                        ->label('Текст')
                        ->html()
                        ->wrap()
                        ->extraAttributes(['class' => 'fi-prose']),
                ])->space(2),
            ])
            ->contentGrid(['default' => 1])
            ->extraAttributes(['class' => 'hb-vacancy-notes'])
            ->recordUrl(null)
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalHeading('Редагувати нотатку')
                    ->using(static function (VacancyNote $record, array $data): VacancyNote {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateVacancyNoteAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->modalHeading('Видалити нотатку')
                    ->using(static fn (VacancyNote $record): bool => app(DeleteVacancyNoteAction::class)->run($record->id)),
            ])
            ->emptyStateHeading('Нотаток ще немає')
            ->emptyStateDescription('Записуйте сюди листи, результати співбесід, питання й відповіді.')
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleBottomCenterText);
    }

    /**
     * @param Builder<VacancyNote> $query
     *
     * @return Builder<VacancyNote>
     */
    private static function newestFirst(Builder $query): Builder
    {
        $query->getQuery()->orderByDesc('occurred_at')->orderByDesc('id');

        return $query;
    }
}
