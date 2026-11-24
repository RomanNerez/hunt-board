<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages;

use App\Containers\AppSection\Vacancy\Actions\DeleteVacancyAction;
use App\Containers\AppSection\Vacancy\Actions\UpdateVacancyAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use App\Containers\AppSection\Vacancy\UI\Filament\Support\VacancySourceLink;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
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
                    'sources' => self::sourcesFormState($record),
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

    /**
     * @param Vacancy $record
     *
     * @return list<array{vacancy_source_id: int, url: string|null}>
     */
    private static function sourcesFormState(Vacancy $record): array
    {
        return array_values($record->vacancySources
            ->map(static fn (VacancySource $source): array => [
                'vacancy_source_id' => $source->id,
                'url' => VacancySourceLink::url($source),
            ])
            ->all());
    }
}
