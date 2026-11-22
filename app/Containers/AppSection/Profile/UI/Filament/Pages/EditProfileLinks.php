<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Actions\SyncProfileLinksAction;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EditProfileLinks extends ProfileFormPage
{
    /**
     * @var string|null
     */
    protected static string|null $slug = 'links';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 20;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Посилання';

    /**
     * @var string|null
     */
    protected static string|null $title = 'Посилання';

    /**
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill([
            'links' => ProfileLink::query()
                ->where('user_id', $this->userId())
                ->orderBy('sort')
                ->get()
                ->map(static fn (ProfileLink $link): array => [
                    'label' => self::localeValues($link->getTranslations('label')),
                    'url' => self::localeValues($link->getTranslations('url')),
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
                Repeater::make('links')
                    ->hiddenLabel()
                    ->schema([
                        ...$this->translatable('label', static fn (string $name): TextInput => TextInput::make($name)
                            ->label('Назва')
                            ->maxLength(100)
                            ->datalist(['LinkedIn', 'GitHub', 'Portfolio', 'Telegram']), required: true),
                        ...$this->translatable('url', static fn (string $name): TextInput => TextInput::make($name)
                            ->label('URL')
                            ->url()
                            ->rule('url:http,https')
                            ->maxLength(2048), required: true),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Додати посилання')
                    ->itemLabel(fn (array $state): string|null => self::translationWithFallback($state['label'] ?? null, $this->activeDataLocale())),
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

        app(SyncProfileLinksAction::class)->run($this->userId(), $this->repeaterItems($state, 'links'));

        $this->notifySaved();
    }
}
