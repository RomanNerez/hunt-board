<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Pages;

use App\Containers\AppSection\Company\Actions\CreateDomainAction;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\DomainResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManageDomains extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = DomainResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Створити домен')
                ->using(static function (array $data): Domain {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateDomainAction::class)->run($attributes);
                }),
        ];
    }
}
