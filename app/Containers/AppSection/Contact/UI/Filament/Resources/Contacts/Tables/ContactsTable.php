<?php

namespace App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Tables;

use App\Containers\AppSection\Contact\Actions\DeleteContactAction;
use App\Containers\AppSection\Contact\Actions\UpdateContactAction;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas\VacancySourcesRepeater;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ContactsTable
{
    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with(['vacancySources', 'vacancies']))
            ->columns([
                TextColumn::make('name')
                    ->label('Ім\'я')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vacancySources.name')
                    ->label('Джерела')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('vacancies.title')
                    ->label('Вакансії')
                    ->badge()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('—'),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->mutateRecordDataUsing(static fn (Contact $record, array $data): array => [
                        ...$data,
                        'sources' => VacancySourcesRepeater::stateFrom($record->vacancySources),
                    ])
                    ->using(static function (Contact $record, array $data): Contact {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateContactAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->using(static fn (Contact $record): bool => app(DeleteContactAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані контакти')
                        ->using(static function (Collection $records): void {
                            /** @var Contact $record */
                            foreach ($records as $record) {
                                app(DeleteContactAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
