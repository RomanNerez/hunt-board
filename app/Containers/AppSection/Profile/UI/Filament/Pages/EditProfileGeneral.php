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

        $this->form->fill($profile?->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'location',
            'headline',
            'summary',
        ]) ?? ['email' => Auth::user()?->getAttribute('email')]);
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
                TextInput::make('first_name')
                    ->label("Ім'я")
                    ->required()
                    ->maxLength(100),
                TextInput::make('last_name')
                    ->label('Прізвище')
                    ->required()
                    ->maxLength(100),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->telRegex('/^\+?[0-9\s\-()]{7,32}$/')
                    ->maxLength(32),
                TextInput::make('location')
                    ->label('Локація')
                    ->maxLength(255),
                TextInput::make('headline')
                    ->label('Заголовок')
                    ->placeholder('Senior FullStack Developer')
                    ->maxLength(255),
                Textarea::make('summary')
                    ->label('Про себе')
                    ->rows(8)
                    ->maxLength(5000)
                    ->columnSpanFull(),
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
