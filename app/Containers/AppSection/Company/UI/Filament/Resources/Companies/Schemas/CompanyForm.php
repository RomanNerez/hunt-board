<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Schemas;

use App\Containers\AppSection\Company\Actions\CreateCompanyTypeAction;
use App\Containers\AppSection\Company\Actions\CreateDomainAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Models\Domain;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class CompanyForm
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
                TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('company_type_id')
                    ->label('Тип компанії')
                    ->options(static fn (): array => CompanyType::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->createOptionForm([self::dictionaryNameInput(CompanyType::class)])
                    ->createOptionModalHeading('Створити тип компанії')
                    ->createOptionUsing(static function (array $data): int {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(CreateCompanyTypeAction::class)->run($attributes)->id;
                    }),
                Select::make('domains')
                    ->label('Домени')
                    ->multiple()
                    ->options(static fn (): array => Domain::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->createOptionForm([self::dictionaryNameInput(Domain::class)])
                    ->createOptionModalHeading('Створити домен')
                    ->createOptionUsing(static function (array $data): int {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(CreateDomainAction::class)->run($attributes)->id;
                    }),
                self::urlInput('website_url', 'Сайт'),
                self::urlInput('vacancies_url', 'Сторінка вакансій'),
                Textarea::make('description')
                    ->label('Опис')
                    ->maxLength(1000)
                    ->rows(4)
                    ->columnSpanFull(),
                FileUpload::make('logo')
                    ->label('Логотип')
                    ->image()
                    ->acceptedFileTypes(Company::LOGO_MIME_TYPES)
                    ->maxSize(2048)
                    ->disk('public')
                    ->storeFiles(false)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    /**
     * @param class-string<CompanyType|Domain> $model
     *
     * @return TextInput
     */
    private static function dictionaryNameInput(string $model): TextInput
    {
        return TextInput::make('name')
            ->label('Назва')
            ->required()
            ->maxLength(255)
            ->unique($model, 'name');
    }

    /**
     * @param string $name
     * @param string $label
     *
     * @return TextInput
     */
    private static function urlInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->url()
            ->rule('url:http,https')
            ->maxLength(2048);
    }
}
