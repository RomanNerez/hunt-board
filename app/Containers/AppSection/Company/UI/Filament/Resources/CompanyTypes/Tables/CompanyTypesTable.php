<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Tables;

use App\Containers\AppSection\Company\Actions\DeleteCompanyTypeAction;
use App\Containers\AppSection\Company\Actions\UpdateCompanyTypeAction;
use App\Containers\AppSection\Company\Models\CompanyType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

final class CompanyTypesTable
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
                    ->using(static function (CompanyType $record, array $data): CompanyType {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateCompanyTypeAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->using(static fn (CompanyType $record): bool => app(DeleteCompanyTypeAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані типи компаній')
                        ->using(static function (Collection $records): void {
                            /** @var CompanyType $record */
                            foreach ($records as $record) {
                                app(DeleteCompanyTypeAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
