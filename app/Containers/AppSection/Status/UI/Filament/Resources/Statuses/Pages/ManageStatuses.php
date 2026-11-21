<?php

namespace App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Pages;

use App\Containers\AppSection\Status\Actions\CreateStatusAction;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\StatusResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManageStatuses extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = StatusResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(static function (array $data): Status {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateStatusAction::class)->run($attributes);
                }),
        ];
    }
}
