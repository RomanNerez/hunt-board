<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas;

use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

final class WorkExperienceForm
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
                Actions::make(ManageWorkExperiences::dataLocaleActions())
                    ->key('dataLocale')
                    ->columnSpanFull(),
                ...ManageWorkExperiences::translatable('company_name', static fn (string $name): TextInput => TextInput::make($name)
                    ->label('Компанія / проєкт')
                    ->maxLength(255), required: true),
                Select::make('type')
                    ->label('Тип')
                    ->options(WorkExperienceType::class)
                    ->required(),
                ...ManageWorkExperiences::translatable('role', static fn (string $name): TextInput => TextInput::make($name)
                    ->label('Роль')
                    ->maxLength(255), required: true),
                DatePicker::make('started_on')
                    ->label('Дата початку')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->required(),
                DatePicker::make('finished_on')
                    ->label('Дата завершення')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->afterOrEqual('started_on'),
            ])
            ->columns(2);
    }

    /**
     * @param ProfileWorkExperience $record
     *
     * @return array<string, mixed>
     */
    public static function recordState(ProfileWorkExperience $record): array
    {
        return [
            'company_name' => ManageWorkExperiences::localeValues($record->getTranslations('company_name')),
            'type' => $record->type->value,
            'role' => ManageWorkExperiences::localeValues($record->getTranslations('role')),
            'started_on' => $record->started_on->toDateString(),
            'finished_on' => $record->finished_on?->toDateString(),
        ];
    }
}
