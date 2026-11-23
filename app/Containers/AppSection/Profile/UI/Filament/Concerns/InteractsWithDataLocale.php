<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Concerns;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Field;
use Illuminate\Validation\ValidationException;

trait InteractsWithDataLocale
{
    /**
     * @var string
     */
    public string $dataLocale = ProfileLocale::Ukrainian->value;

    /**
     * @param string $name
     * @param \Closure(string, ProfileLocale): Field $field
     * @param bool $required
     *
     * @return list<Field>
     */
    public static function translatable(string $name, \Closure $field, bool $required = false): array
    {
        return array_map(
            static fn (ProfileLocale $locale): Field => $field("{$name}.{$locale->value}", $locale)
                ->required($required && $locale->isPrimary())
                ->visibleJs("\$wire.dataLocale === '{$locale->value}'"),
            ProfileLocale::cases(),
        );
    }

    /**
     * @param mixed $translations
     * @param ProfileLocale $locale
     *
     * @return string|null
     */
    public static function translationWithFallback(mixed $translations, ProfileLocale $locale): string|null
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
     * @return ProfileLocale
     */
    public function activeDataLocale(): ProfileLocale
    {
        return ProfileLocale::tryFrom($this->dataLocale) ?? ProfileLocale::primary();
    }

    /**
     * @return list<Action>
     */
    public static function dataLocaleActions(): array
    {
        return array_map(
            static fn (ProfileLocale $locale): Action => Action::make("dataLocale_{$locale->value}")
                ->label($locale->getLabel())
                ->color(static fn (self $livewire): string => $livewire->dataLocale === $locale->value ? 'primary' : 'gray')
                ->action(static function (self $livewire) use ($locale): void {
                    $livewire->dataLocale = $locale->value;
                }),
            ProfileLocale::cases(),
        );
    }

    /**
     * @return ActionGroup
     */
    protected function dataLocaleActionGroup(): ActionGroup
    {
        return ActionGroup::make(static::dataLocaleActions())->buttonGroup();
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
     * @param array<array-key, mixed> $translations
     *
     * @return array<string, string|null>
     */
    public static function localeValues(array $translations): array
    {
        $values = [];

        foreach (ProfileLocale::cases() as $locale) {
            $value = $translations[$locale->value] ?? null;
            $values[$locale->value] = is_string($value) ? $value : null;
        }

        return $values;
    }
}
