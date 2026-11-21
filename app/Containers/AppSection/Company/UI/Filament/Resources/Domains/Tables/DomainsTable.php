<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Tables;

use App\Containers\AppSection\Company\Actions\DeleteDomainAction;
use App\Containers\AppSection\Company\Actions\UpdateDomainAction;
use App\Containers\AppSection\Company\Models\Domain;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

final class DomainsTable
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
                    ->using(static function (Domain $record, array $data): Domain {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateDomainAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->using(static fn (Domain $record): bool => app(DeleteDomainAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані домени')
                        ->using(static function (Collection $records): void {
                            /** @var Domain $record */
                            foreach ($records as $record) {
                                app(DeleteDomainAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
