<?php

namespace App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Tables;

use App\Containers\AppSection\VacancySource\Actions\DeleteVacancySourceAction;
use App\Containers\AppSection\VacancySource\Actions\UpdateVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

final class VacancySourcesTable
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
                TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->using(static function (VacancySource $record, array $data): VacancySource {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateVacancySourceAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->using(static fn (VacancySource $record): bool => app(DeleteVacancySourceAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані джерела вакансій')
                        ->using(static function (Collection $records): void {
                            /** @var VacancySource $record */
                            foreach ($records as $record) {
                                app(DeleteVacancySourceAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
