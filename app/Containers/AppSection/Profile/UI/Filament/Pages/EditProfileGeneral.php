<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Actions\UpdateUserProfileAction;
use App\Containers\AppSection\Profile\Models\UserProfile;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

final class EditProfileGeneral extends ProfileFormPage
{
    /**
     * @var string|null
     */
    protected static string|null $slug = 'general';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 10;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Загальне';

    /**
     * @var string|null
     */
    protected static string|null $title = 'Загальне';

    /**
     * @return void
     */
    public function mount(): void
    {
        $profile = UserProfile::query()->where('user_id', $this->userId())->first();

        if (null === $profile) {
            $this->form->fill(['email' => Auth::user()?->getAttribute('email')]);

            return;
        }

        $this->form->fill([
            ...$profile->only(['email', 'phone']),
            ...array_map(
                static fn (string $field): array => self::localeValues($profile->getTranslations($field)),
                array_combine($profile->translatable, $profile->translatable),
            ),
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
            ->columns(2)
            ->components([
                ...$this->translatable('first_name', static fn (string $name): TextInput => TextInput::make($name)
                    ->label("Ім'я")
                    ->maxLength(100), required: true),
                ...$this->translatable('last_name', static fn (string $name): TextInput => TextInput::make($name)
                    ->label('Прізвище')
                    ->maxLength(100), required: true),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->telRegex('/^\+?[0-9\s\-()]{7,32}$/')
                    ->maxLength(32),
                ...$this->translatable('location', static fn (string $name): TextInput => TextInput::make($name)
                    ->label('Локація')
                    ->maxLength(255)),
                ...$this->translatable('headline', static fn (string $name): TextInput => TextInput::make($name)
                    ->label('Заголовок')
                    ->placeholder('Senior FullStack Developer')
                    ->maxLength(255)),
                ...$this->translatable('summary', static fn (string $name): Textarea => Textarea::make($name)
                    ->label('Про себе')
                    ->rows(8)
                    ->maxLength(5000)
                    ->columnSpanFull()),
            ]);
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public function save(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->form->getState();

        app(UpdateUserProfileAction::class)->run($this->userId(), $data);

        $this->notifySaved();
    }
}
