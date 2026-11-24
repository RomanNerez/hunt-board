<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use App\Containers\AppSection\Resume\Models\ResumeEducation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

final class ResumeEducationsRepeater
{
    /**
     * @return Repeater
     */
    public static function make(): Repeater
    {
        return Repeater::make('educations')
            ->label('Освіта')
            ->schema([
                TextInput::make('institution')
                    ->label('Навчальний заклад')
                    ->required()
                    ->maxLength(255),
                TextInput::make('institution_url')
                    ->label('Сайт закладу')
                    ->startsWith(['http://', 'https://'])
                    ->maxLength(2048),
                TextInput::make('degree')
                    ->label('Ступінь')
                    ->maxLength(255),
                TextInput::make('field_of_study')
                    ->label('Спеціальність або курс')
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Локація')
                    ->maxLength(255),
                Toggle::make('is_visible')
                    ->label('Показувати')
                    ->default(true)
                    ->inline(false),
                DatePicker::make('start_date')
                    ->label('Початок')
                    ->native(false)
                    ->displayFormat('m.Y'),
                DatePicker::make('end_date')
                    ->label('Завершення')
                    ->native(false)
                    ->displayFormat('m.Y')
                    ->afterOrEqual('start_date'),
                RichEditor::make('description')
                    ->label('Опис')
                    ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                    ->fileAttachments(false)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->reorderableWithDragAndDrop()
            ->collapsible()
            ->itemLabel(static function (array $state): string|null {
                $institution = is_string($state['institution'] ?? null) ? $state['institution'] : '';
                $field = is_string($state['field_of_study'] ?? null) ? $state['field_of_study'] : '';
                $label = trim($institution . ('' !== $institution && '' !== $field ? ' — ' : '') . $field);

                return '' === $label ? null : $label;
            })
            ->addActionLabel('Додати освіту')
            ->columnSpanFull();
    }

    /**
     * @param Collection<int, ResumeEducation> $educations
     *
     * @return list<array<string, mixed>>
     */
    public static function stateFrom(Collection $educations): array
    {
        return array_values($educations
            ->map(static fn (ResumeEducation $education): array => [
                'institution' => $education->institution,
                'institution_url' => $education->institution_url,
                'degree' => $education->degree,
                'field_of_study' => $education->field_of_study,
                'location' => $education->location,
                'start_date' => $education->start_date?->toDateString(),
                'end_date' => $education->end_date?->toDateString(),
                'description' => $education->description,
                'is_visible' => $education->is_visible,
            ])
            ->all());
    }
}
