<?php

namespace App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Tables;

use App\Containers\AppSection\Stack\Actions\DeleteStackAction;
use App\Containers\AppSection\Stack\Actions\UpdateStackAction;
use App\Containers\AppSection\Stack\Models\Stack;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

final class StacksTable
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
                    ->modalHeading('Редагувати технологію')
                    ->using(static function (Stack $record, array $data): Stack {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateStackAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->modalDescription('Технологію буде прибрано з усіх груп навичок і проєктів. Самі групи й проєкти залишаться.')
                    ->using(static fn (Stack $record): bool => app(DeleteStackAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані технології')
                        ->modalDescription('Технології буде прибрано з усіх груп навичок і проєктів. Самі групи й проєкти залишаться.')
                        ->using(static function (Collection $records): void {
                            /** @var Stack $record */
                            foreach ($records as $record) {
                                app(DeleteStackAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
