<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\UI\Filament\Clusters\Profile\ProfileCluster;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @property Schema $form
 */
abstract class ProfileFormPage extends Page
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = ProfileCluster::class;

    /**
     * @var array<string, mixed>|null
     */
    public array|null $data = [];

    /**
     * @var string
     */
    public string $dataLocale = ProfileLocale::Ukrainian->value;

    /**
     * @return void
     */
    abstract public function save(): void;

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Зберегти')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->key('form-actions'),
                ]),
        ]);
    }

    /**
     * @return list<ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(array_map(
                fn (ProfileLocale $locale): Action => Action::make("dataLocale_{$locale->value}")
                    ->label($locale->getLabel())
                    ->color(fn (): string => $this->dataLocale === $locale->value ? 'primary' : 'gray')
                    ->action(function () use ($locale): void {
                        $this->dataLocale = $locale->value;
                    }),
                ProfileLocale::cases(),
            ))->buttonGroup(),
        ];
    }

    /**
     * @param string $name
     * @param \Closure(string, ProfileLocale): Field $field
     * @param bool $required
     *
     * @return list<Field>
     */
    protected function translatable(string $name, \Closure $field, bool $required = false): array
    {
        return array_map(
            static fn (ProfileLocale $locale): Field => $field("{$name}.{$locale->value}", $locale)
                ->required($required && $locale->isPrimary())
                ->visibleJs("\$wire.dataLocale === '{$locale->value}'"),
            ProfileLocale::cases(),
        );
    }

    /**
     * @param ValidationException $exception
     *
     * @return void
     */
    protected function onValidationError(ValidationException $exception): void
    {
        parent::onValidationError($exception);

        $firstErrorKey = (string) array_key_first($exception->errors());

        foreach (ProfileLocale::cases() as $locale) {
            if (str_ends_with($firstErrorKey, ".{$locale->value}")) {
                $this->dataLocale = $locale->value;

                return;
            }
        }
    }

    /**
     * @return ProfileLocale
     */
    protected function activeDataLocale(): ProfileLocale
    {
        return ProfileLocale::tryFrom($this->dataLocale) ?? ProfileLocale::primary();
    }

    /**
     * @param array<array-key, mixed> $translations
     *
     * @return array<string, string|null>
     */
    protected static function localeValues(array $translations): array
    {
        $values = [];

        foreach (ProfileLocale::cases() as $locale) {
            $value = $translations[$locale->value] ?? null;
            $values[$locale->value] = is_string($value) ? $value : null;
        }

        return $values;
    }

    /**
     * @param mixed $translations
     * @param ProfileLocale $locale
     *
     * @return string|null
     */
    protected static function translationWithFallback(mixed $translations, ProfileLocale $locale): string|null
    {
        if (!is_array($translations)) {
            return null;
        }

        foreach ([$locale, ProfileLocale::primary()] as $candidate) {
            $text = $translations[$candidate->value] ?? null;

            if (is_string($text) && '' !== trim($text)) {
                return $text;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    protected function userId(): int
    {
        $id = Auth::id();

        return is_numeric($id) ? (int) $id : 0;
    }

    /**
     * @param array<array-key, mixed> $state
     * @param string $key
     *
     * @return list<array<string, mixed>>
     */
    protected function repeaterItems(array $state, string $key): array
    {
        $items = [];

        foreach (is_array($state[$key] ?? null) ? $state[$key] : [] as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $attributes */
                $attributes = $item;
                $items[] = $attributes;
            }
        }

        return $items;
    }

    /**
     * @return void
     */
    protected function notifySaved(): void
    {
        Notification::make()
            ->success()
            ->title('Профіль збережено')
            ->send();
    }
}
