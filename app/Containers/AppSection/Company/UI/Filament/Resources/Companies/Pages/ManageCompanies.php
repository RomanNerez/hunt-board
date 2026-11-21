<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Pages;

use App\Containers\AppSection\Company\Actions\CreateCompanyAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

final class ManageCompanies extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = CompanyResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Створити компанію')
                ->modalWidth(Width::ThreeExtraLarge)
                ->using(static function (array $data): Company {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateCompanyAction::class)->run($attributes);
                }),
        ];
    }
}
