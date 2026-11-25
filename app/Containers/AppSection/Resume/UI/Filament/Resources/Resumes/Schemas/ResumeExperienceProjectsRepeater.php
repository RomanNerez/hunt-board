<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Containers\AppSection\Stack\UI\Filament\Components\StackSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

final class ResumeExperienceProjectsRepeater
{
    /**
     * @return Repeater
     */
    public static function make(): Repeater
    {
        return Repeater::make('projects')
            ->label('Проєкти')
            ->schema([
                TextInput::make('name')
                    ->label('Назва проєкту')
                    ->required()
                    ->maxLength(255),
                TextInput::make('role')
                    ->label('Роль')
                    ->maxLength(255),
                Toggle::make('is_visible')
                    ->label('Показувати')
                    ->default(true)
                    ->inline(false),
                StackSelect::make('stacks', 'Технології')
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Опис')
                    ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                    ->fileAttachments(false)
                    ->columnSpanFull(),
            ])
            ->columns(3)
            ->defaultItems(0)
            ->reorderableWithDragAndDrop()
            ->collapsible()
            ->collapsed(static fn (Schema|null $item): bool => self::isSaved($item))
            ->itemLabel(static function (array $state): string|null {
                $name = is_string($state['name'] ?? null) ? trim($state['name']) : '';

                return '' === $name ? null : $name;
            })
            ->addActionLabel('Додати проєкт')
            ->columnSpanFull();
    }

    /**
     * @param Collection<int, ResumeExperienceProject> $projects
     *
     * @return list<array<string, mixed>>
     */
    public static function stateFrom(Collection $projects): array
    {
        return array_values($projects
            ->map(static fn (ResumeExperienceProject $project): array => [
                'name' => $project->name,
                'role' => $project->role,
                'is_visible' => $project->is_visible,
                'stacks' => $project->stacks->modelKeys(),
                'description' => $project->description,
            ])
            ->all());
    }

    /**
     * @param Schema|null $item
     *
     * @return bool
     */
    private static function isSaved(Schema|null $item): bool
    {
        $state = $item?->getRawState();
        $state = $state instanceof Arrayable ? $state->toArray() : $state;

        return is_array($state) && is_string($state['name'] ?? null) && '' !== trim($state['name']);
    }
}
