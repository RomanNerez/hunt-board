<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Pages;

use App\Containers\AppSection\Company\Actions\CreateCompanyTypeAction;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\CompanyTypeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManageCompanyTypes extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = CompanyTypeResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Створити тип компанії')
                ->using(static function (array $data): CompanyType {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateCompanyTypeAction::class)->run($attributes);
                }),
        ];
    }
}
