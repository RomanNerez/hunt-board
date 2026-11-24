<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages;

use App\Containers\AppSection\Vacancy\Actions\CreateVacancyAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

final class ListVacancies extends ListRecords
{
    /**
     * @var string
     */
    protected static string $resource = VacancyResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->modalHeading('Створити вакансію')
                ->modalWidth(Width::FiveExtraLarge)
                ->createAnother(false)
                ->using(static function (array $data): Vacancy {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateVacancyAction::class)->run($attributes);
                })
                ->successRedirectUrl(static fn (Vacancy $record): string => VacancyResource::getUrl('view', ['record' => $record])),
        ];
    }
}
