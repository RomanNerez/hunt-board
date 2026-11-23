<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperienceProjects;
use App\Containers\AppSection\Stack\UI\Filament\Components\StackSelect;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

final class ProjectForm
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
                Actions::make(ManageWorkExperienceProjects::dataLocaleActions())
                    ->key('dataLocale')
                    ->columnSpanFull(),
                ...ManageWorkExperienceProjects::translatable('name', static fn (string $name): TextInput => TextInput::make($name)
                    ->label('Назва проєкту')
                    ->maxLength(255)
                    ->columnSpanFull(), required: true),
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
                StackSelect::make('stacks', 'Стек')
                    ->columnSpanFull(),
                ...ManageWorkExperienceProjects::translatable('description', static fn (string $name): RichEditor => RichEditor::make($name)
                    ->label('Опис')
                    ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                    ->fileAttachments(false)
                    ->maxLength(20000)
                    ->columnSpanFull()),
            ])
            ->columns(2);
    }

    /**
     * @param ProfileProject $record
     *
     * @return array<string, mixed>
     */
    public static function recordState(ProfileProject $record): array
    {
        return [
            'name' => ManageWorkExperienceProjects::localeValues($record->getTranslations('name')),
            'started_on' => $record->started_on->toDateString(),
            'finished_on' => $record->finished_on?->toDateString(),
            'stacks' => $record->stacks->modelKeys(),
            'description' => ManageWorkExperienceProjects::localeValues($record->getTranslations('description')),
        ];
    }
}
