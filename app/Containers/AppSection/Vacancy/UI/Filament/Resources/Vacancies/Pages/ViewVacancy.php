<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages;

use App\Containers\AppSection\Vacancy\Actions\DeleteVacancyAction;
use App\Containers\AppSection\Vacancy\Actions\UpdateVacancyAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas\VacancySourcesRepeater;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

final class ViewVacancy extends ViewRecord
{
    /**
     * @var string
     */
    protected static string $resource = VacancyResource::class;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Огляд';

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->slideOver()
                ->modalHeading('Редагувати вакансію')
                ->modalWidth(Width::ThreeExtraLarge)
                ->mutateRecordDataUsing(static fn (Vacancy $record, array $data): array => [
                    ...$data,
                    'sources' => VacancySourcesRepeater::stateFrom($record->vacancySources),
                    'contacts' => $record->contacts->modelKeys(),
                ])
                ->using(static function (Vacancy $record, array $data): Vacancy {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    app(UpdateVacancyAction::class)->run($record->id, $attributes);

                    return $record->refresh()->load(Vacancy::DISPLAY_RELATIONS);
                }),
            DeleteAction::make()
                ->using(static fn (Vacancy $record): bool => app(DeleteVacancyAction::class)->run($record->id))
                ->successRedirectUrl(VacancyResource::getUrl('index')),
        ];
    }
}
