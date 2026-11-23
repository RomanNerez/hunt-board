<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Tables;

use App\Containers\AppSection\Profile\Actions\DeleteProfileProjectAction;
use App\Containers\AppSection\Profile\Actions\UpdateProfileProjectAction;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperienceProjects;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas\ProjectForm;
use App\Containers\AppSection\Profile\UI\Filament\Support\MountedActionValidation;
use App\Containers\AppSection\Profile\UI\Filament\Support\PeriodFormatter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProjectsTable
{
    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->modelLabel('проєкт')
            ->pluralModelLabel('Проєкти')
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with('workExperience'))
            ->columns([
                TextColumn::make('name')
                    ->label('Проєкт')
                    ->getStateUsing(static fn (ProfileProject $record, HasTable $livewire): string|null => self::translated($record, 'name', $livewire)),
                TextColumn::make('period')
                    ->label('Період')
                    ->getStateUsing(static fn (ProfileProject $record): string => PeriodFormatter::monthYear($record->started_on, $record->finished_on))
                    ->sortable(query: static fn (Builder $query, string $direction): Builder => $query->reorder('started_on', $direction)->orderBy('id', $direction)),
                TextColumn::make('technologies')
                    ->label('Стек')
                    ->badge()
                    ->limitList(4),
            ])
            ->defaultSort(static fn (Builder $query): Builder => $query->reorder()->orderByDesc('started_on')->orderByDesc('id'))
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Редагувати проєкт')
                    ->slideOver()
                    ->fillForm(static fn (ProfileProject $record): array => ProjectForm::recordState($record))
                    ->using(static function (ProfileProject $record, array $data, Action $action): ProfileProject {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return MountedActionValidation::run($action, static fn (): ProfileProject => app(UpdateProfileProjectAction::class)->run($record->id, $attributes));
                    }),
                DeleteAction::make()
                    ->modalHeading('Видалити проєкт')
                    ->using(static fn (ProfileProject $record): bool => app(DeleteProfileProjectAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані проєкти')
                        ->using(static function (Collection $records): void {
                            /** @var ProfileProject $record */
                            foreach ($records as $record) {
                                app(DeleteProfileProjectAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }

    /**
     * @param ProfileProject $record
     * @param string $attribute
     * @param HasTable $livewire
     *
     * @return string|null
     */
    private static function translated(ProfileProject $record, string $attribute, HasTable $livewire): string|null
    {
        $locale = $livewire instanceof ManageWorkExperienceProjects ? $livewire->activeDataLocale() : ProfileLocale::primary();

        return ManageWorkExperienceProjects::translationWithFallback($record->getTranslations($attribute), $locale);
    }
}
