<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas;

use App\Containers\AppSection\Company\Actions\CreateCompanyAction;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\VacancySource\Actions\CreateVacancySourceAction;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas\VacancySourceForm;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

final class VacancyForm
{
    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основне')
                    ->schema([
                        TextInput::make('title')
                            ->label('Назва')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        self::companySelect(),
                        Select::make('status_id')
                            ->label('Статус')
                            ->relationship('status', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('location')
                            ->label('Локація')
                            ->maxLength(255),
                        Select::make('work_format')
                            ->label('Формат роботи')
                            ->options(WorkFormat::class),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Джерела')
                    ->schema([
                        self::sourcesRepeater(),
                    ])
                    ->columnSpanFull(),
                Section::make('Умови')
                    ->schema([
                        TextInput::make('salary_min')
                            ->label('Зарплата від')
                            ->integer()
                            ->minValue(0),
                        TextInput::make('salary_max')
                            ->label('Зарплата до')
                            ->integer()
                            ->minValue(0)
                            ->gte(static fn (Get $get): string|null => filled($get('salary_min')) ? 'salary_min' : null),
                        Select::make('currency')
                            ->label('Валюта')
                            ->options(Currency::class)
                            ->requiredWith('salary_min,salary_max'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Дати')
                    ->schema([
                        DatePicker::make('applied_at')
                            ->label('Дата відгуку'),
                        DateTimePicker::make('next_action_at')
                            ->label('Наступна дія')
                            ->seconds(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Опис і нотатки')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Опис')
                            ->toolbarButtons([
                                ['bold', 'italic', 'link'],
                                ['h2', 'h3'],
                                ['bulletList', 'orderedList', 'blockquote'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachments(false)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Нотатки')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return Select
     */
    private static function companySelect(): Select
    {
        return Select::make('company_id')
            ->label('Компанія')
            ->relationship('company', 'name')
            ->searchable()
            ->preload()
            ->required()
            ->createOptionForm(static fn (Schema $schema): Schema => CompanyForm::configure($schema))
            ->createOptionModalHeading('Створити компанію')
            ->createOptionAction(static fn (Action $action): Action => $action->modalWidth(Width::ThreeExtraLarge))
            ->createOptionUsing(static function (array $data): int {
                /** @var array<string, mixed> $attributes */
                $attributes = $data;

                return app(CreateCompanyAction::class)->run($attributes)->id;
            });
    }

    /**
     * @return Repeater
     */
    private static function sourcesRepeater(): Repeater
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
