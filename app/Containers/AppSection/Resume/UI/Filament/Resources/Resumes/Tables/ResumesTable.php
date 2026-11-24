<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Tables;

use App\Containers\AppSection\Resume\Actions\DeleteResumeAction;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\CreateAction;
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

final class ResumesTable
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
                        TextColumn::make('first_name')
                            ->label('Імʼя')
                            ->formatStateUsing(static fn (Resume $record): string => "{$record->first_name} {$record->last_name}")
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large),
                        TextColumn::make('created_at')
                            ->label('Створено')
                            ->dateTime('d.m.Y')
                            ->icon(Heroicon::OutlinedCalendar)
                            ->color('gray')
                            ->alignEnd()
                            ->grow(false),
                    ])->from('sm'),
                    TextColumn::make('headline')
                        ->label('Заголовок')
                        ->color('gray'),
                    TextColumn::make('summary')
                        ->label('Короткий опис')
                        ->wrap(),
                ])->space(2),
            ])
            ->contentGrid(['default' => 1])
            ->extraAttributes(['class' => 'hb-resume-cards'])
            ->recordUrl(static fn (Resume $record): string => ResumeResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Редагувати'),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Видалити')
                    ->modalHeading('Видалити резюме')
                    ->using(static fn (Resume $record): bool => app(DeleteResumeAction::class)->run($record->id)),
            ])
            ->emptyStateHeading('Резюме ще немає')
            ->emptyStateDescription('Створіть основне резюме або версію під конкретну вакансію.')
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Створити резюме')
                    ->url(static fn (): string => ResumeResource::getUrl('create')),
            ]);
    }

    /**
     * @param Builder<Resume> $query
     *
     * @return Builder<Resume>
     */
    private static function newestFirst(Builder $query): Builder
    {
        $query->getQuery()->orderByDesc('updated_at')->orderByDesc('id');

        return $query;
    }
}
