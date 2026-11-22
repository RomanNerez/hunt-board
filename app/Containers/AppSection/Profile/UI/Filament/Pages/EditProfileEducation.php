<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Actions\SyncProfileEducationAction;
use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EditProfileEducation extends ProfileFormPage
{
    /**
     * @var string|null
     */
    protected static string|null $slug = 'education';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 30;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Освіта';

    /**
     * @var string|null
     */
    protected static string|null $title = 'Освіта';

    /**
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill([
            'education' => ProfileEducation::query()
                ->where('user_id', $this->userId())
                ->orderBy('sort')
                ->get()
                ->map(static fn (ProfileEducation $education): array => [
                    'type' => $education->type->value,
                    'title' => self::localeValues($education->getTranslations('title')),
                    'institution' => self::localeValues($education->getTranslations('institution')),
                    'started_on' => $education->started_on?->toDateString(),
                    'finished_on' => $education->finished_on?->toDateString(),
                    'url' => $education->url,
                    'description' => self::localeValues($education->getTranslations('description')),
                ])
                ->all(),
        ]);
    }

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Repeater::make('education')
                    ->hiddenLabel()
                    ->schema([
                        Select::make('type')
                            ->label('Тип')
                            ->options(EducationType::class)
                            ->required(),
                        ...$this->translatable('title', static fn (string $name): TextInput => TextInput::make($name)
                            ->label('Назва')
                            ->maxLength(255), required: true),
                        ...$this->translatable('institution', static fn (string $name): TextInput => TextInput::make($name)
                            ->label('Заклад / організація')
                            ->maxLength(255)),
                        TextInput::make('url')
                            ->label('Посилання')
                            ->url()
                            ->rule('url:http,https')
                            ->maxLength(2048),
                        DatePicker::make('started_on')
                            ->label('Дата початку')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        DatePicker::make('finished_on')
                            ->label('Дата завершення')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->afterOrEqual('started_on'),
                        ...$this->translatable('description', static fn (string $name): Textarea => Textarea::make($name)
                            ->label('Опис')
                            ->maxLength(2000)
                            ->columnSpanFull()),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible()
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Додати запис')
                    ->itemLabel(fn (array $state): string|null => self::itemLabel($state, $this->activeDataLocale())),
            ]);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function save(): void
    {
        $state = $this->form->getState();

        app(SyncProfileEducationAction::class)->run($this->userId(), $this->repeaterItems($state, 'education'));

        $this->notifySaved();
    }

    /**
     * @param array<array-key, mixed> $state
     * @param ProfileLocale $locale
     *
     * @return string|null
     */
    private static function itemLabel(array $state, ProfileLocale $locale): string|null
    {
        $type = $state['type'] ?? null;
        $type = $type instanceof EducationType ? $type : EducationType::tryFrom(is_string($type) ? $type : '');
        $title = self::translationWithFallback($state['title'] ?? null, $locale);

        $parts = array_filter([$type?->getLabel(), $title], static fn (string|null $part): bool => null !== $part && '' !== $part);

        return [] === $parts ? null : implode(' · ', $parts);
    }
}
