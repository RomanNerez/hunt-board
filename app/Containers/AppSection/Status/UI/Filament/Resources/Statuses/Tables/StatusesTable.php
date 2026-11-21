<?php

namespace App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Tables;

use App\Containers\AppSection\Status\Actions\DeleteStatusAction;
use App\Containers\AppSection\Status\Actions\UpdateStatusAction;
use App\Containers\AppSection\Status\Models\Status;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

final class StatusesTable
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
                    ->formatStateUsing(static fn (Status $record): HtmlString => $record->color->renderBadge($record->name))
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->using(static function (Status $record, array $data): Status {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateStatusAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->using(static fn (Status $record): bool => app(DeleteStatusAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(static function (Collection $records): void {
                            /** @var Status $record */
                            foreach ($records as $record) {
                                app(DeleteStatusAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
