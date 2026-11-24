<?php

namespace App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Tables;

use App\Containers\AppSection\Status\Actions\DeleteStatusAction;
use App\Containers\AppSection\Status\Actions\UpdateStatusAction;
use App\Containers\AppSection\Status\Exceptions\StatusInUseException;
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
    public const IN_USE_NOTIFICATION = 'Статус використовується вакансіями';

    public const BULK_IN_USE_MESSAGE = 'Частину статусів не видалено, бо їх використовують вакансії.';

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
                    ->failureNotificationTitle(self::IN_USE_NOTIFICATION)
                    ->using(static fn (Status $record): bool => self::deleteUnlessInUse($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані статуси')
                        ->using(static function (DeleteBulkAction $action, Collection $records): void {
                            /** @var Status $record */
                            foreach ($records as $record) {
                                if (!self::deleteUnlessInUse($record)) {
                                    $action->reportBulkProcessingFailure('status_in_use', self::BULK_IN_USE_MESSAGE);
                                }
                            }
                        }),
                ]),
            ]);
    }

    /**
     * @param Status $status
     *
     * @return bool
     *
     * @throws \Throwable
     */
    private static function deleteUnlessInUse(Status $status): bool
    {
        try {
            return app(DeleteStatusAction::class)->run($status->id);
        } catch (StatusInUseException) {
            return false;
        }
    }
}
