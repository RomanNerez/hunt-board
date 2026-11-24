<?php

namespace App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Pages;

use App\Containers\AppSection\VacancySource\Actions\CreateVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\VacancySourceResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManageVacancySources extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = VacancySourceResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Створити джерело вакансії')
                ->using(static function (array $data): VacancySource {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateVacancySourceAction::class)->run($attributes);
                }),
        ];
    }
}
