<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Tables;

use App\Containers\AppSection\Profile\Actions\DeleteProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Actions\UpdateProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas\WorkExperienceForm;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\WorkExperienceResource;
use App\Containers\AppSection\Profile\UI\Filament\Support\PeriodFormatter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class WorkExperiencesTable
{
    /**
     * @var string
     */
    private const DELETE_WARNING = 'Разом із записом буде видалено всі його проєкти. Цю дію не можна скасувати.';

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Компанія / проєкт')
                    ->getStateUsing(static fn (ProfileWorkExperience $record, HasTable $livewire): string|null => self::translated($record, 'company_name', $livewire)),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('role')
                    ->label('Роль')
                    ->getStateUsing(static fn (ProfileWorkExperience $record, HasTable $livewire): string|null => self::translated($record, 'role', $livewire)),
                TextColumn::make('period')
                    ->label('Період')
                    ->getStateUsing(static fn (ProfileWorkExperience $record): string => PeriodFormatter::monthYear($record->started_on, $record->finished_on))
                    ->sortable(query: static fn (Builder $query, string $direction): Builder => $query->orderBy('started_on', $direction)->orderBy('id', $direction)),
            ])
            ->defaultSort(static fn (Builder $query): Builder => $query->orderByDesc('started_on')->orderByDesc('id'))
            ->recordActions([
                Action::make('projects')
                    ->label('Проєкти')
                    ->icon(Heroicon::OutlinedRectangleStack)
                    ->url(static fn (ProfileWorkExperience $record): string => WorkExperienceResource::getUrl('projects', ['record' => $record])),
                EditAction::make()
                    ->modalHeading('Редагувати досвід роботи')
                    ->fillForm(static fn (ProfileWorkExperience $record): array => WorkExperienceForm::recordState($record))
                    ->using(static function (ProfileWorkExperience $record, array $data): ProfileWorkExperience {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateProfileWorkExperienceAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->modalDescription(self::DELETE_WARNING)
                    ->using(static fn (ProfileWorkExperience $record): bool => app(DeleteProfileWorkExperienceAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані записи досвіду')
                        ->modalDescription(self::DELETE_WARNING)
                        ->using(static function (Collection $records): void {
                            /** @var ProfileWorkExperience $record */
                            foreach ($records as $record) {
                                app(DeleteProfileWorkExperienceAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }

    /**
     * @param ProfileWorkExperience $record
     * @param string $attribute
     * @param HasTable $livewire
     *
     * @return string|null
     */
    private static function translated(ProfileWorkExperience $record, string $attribute, HasTable $livewire): string|null
    {
        $locale = $livewire instanceof ManageWorkExperiences ? $livewire->activeDataLocale() : ProfileLocale::primary();

        return ManageWorkExperiences::translationWithFallback($record->getTranslations($attribute), $locale);
    }
}
