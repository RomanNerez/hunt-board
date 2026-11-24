<?php

namespace App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas;

use App\Containers\AppSection\VacancySource\Actions\CreateVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Support\VacancySourceLink;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

final class VacancySourcesRepeater
{
    /**
     * @return Repeater
     */
    public static function make(): Repeater
    {
        return Repeater::make('sources')
            ->label('Джерела')
            ->hiddenLabel()
            ->schema([
                self::vacancySourceSelect(),
                TextInput::make('url')
                    ->label('Посилання')
                    ->url()
                    ->rule('url:http,https')
                    ->maxLength(2048),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->reorderable(false)
            ->addActionLabel('Додати джерело')
            ->columnSpanFull();
    }

    /**
     * @param Collection<int, VacancySource> $sources
     *
     * @return list<array{vacancy_source_id: int, url: string|null}>
     */
    public static function stateFrom(Collection $sources): array
    {
        return array_values($sources
            ->map(static fn (VacancySource $source): array => [
                'vacancy_source_id' => $source->id,
                'url' => VacancySourceLink::url($source),
            ])
            ->all());
    }

    /**
     * @return Select
     */
    private static function vacancySourceSelect(): Select
    {
        return Select::make('vacancy_source_id')
            ->label('Джерело')
            ->options(static fn (): Collection => VacancySource::query()->pluck('name', 'id')->sort())
            ->searchable()
            ->required()
            ->distinct()
            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
            ->createOptionForm(static fn (Schema $schema): Schema => VacancySourceForm::configure($schema->model(VacancySource::class)))
            ->createOptionModalHeading('Створити джерело вакансії')
            ->createOptionUsing(static function (array $data): int {
                /** @var array<string, mixed> $attributes */
                $attributes = $data;

                return app(CreateVacancySourceAction::class)->run($attributes)->id;
            });
    }
}
