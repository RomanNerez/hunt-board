<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use App\Containers\AppSection\Resume\Enums\ResumeEmploymentType;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

final class ResumeExperiencesRepeater
{
    /**
     * @return Repeater
     */
    public static function make(): Repeater
    {
        return Repeater::make('experiences')
            ->label('Досвід роботи')
            ->hiddenLabel()
            ->schema([
                TextInput::make('company')
                    ->label('Компанія')
                    ->required()
                    ->maxLength(255),
                TextInput::make('company_url')
                    ->label('Сайт компанії')
                    ->startsWith(['http://', 'https://'])
                    ->maxLength(2048),
                TextInput::make('position')
                    ->label('Посада')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Локація')
                    ->maxLength(255),
                Select::make('employment_type')
                    ->label('Тип зайнятості')
                    ->options(ResumeEmploymentType::class),
                Toggle::make('is_visible')
                    ->label('Показувати')
                    ->default(true)
                    ->inline(false),
                DatePicker::make('start_date')
                    ->label('Початок')
                    ->native(false)
                    ->displayFormat('m.Y')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Завершення')
                    ->native(false)
                    ->displayFormat('m.Y')
                    ->afterOrEqual('start_date'),
                ResumeExperienceProjectsRepeater::make(),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->reorderableWithDragAndDrop()
            ->collapsible()
            ->itemLabel(static function (array $state): string|null {
                $company = is_string($state['company'] ?? null) ? $state['company'] : '';
                $position = is_string($state['position'] ?? null) ? $state['position'] : '';
                $label = trim($company . ('' !== $company && '' !== $position ? ' — ' : '') . $position);

                return '' === $label ? null : $label;
            })
            ->addActionLabel('Додати досвід')
            ->columnSpanFull();
    }

    /**
     * @param Collection<int, ResumeExperience> $experiences
     *
     * @return list<array<string, mixed>>
     */
    public static function stateFrom(Collection $experiences): array
    {
        return array_values($experiences
            ->map(static fn (ResumeExperience $experience): array => [
                'company' => $experience->company,
                'company_url' => $experience->company_url,
                'position' => $experience->position,
                'location' => $experience->location,
                'employment_type' => $experience->employment_type?->value,
                'start_date' => $experience->start_date->toDateString(),
                'end_date' => $experience->end_date?->toDateString(),
                'is_visible' => $experience->is_visible,
                'projects' => ResumeExperienceProjectsRepeater::stateFrom($experience->projects),
            ])
            ->all());
    }
}
