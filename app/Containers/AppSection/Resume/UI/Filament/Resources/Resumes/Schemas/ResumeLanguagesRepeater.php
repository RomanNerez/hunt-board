<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use App\Containers\AppSection\Resume\Enums\ResumeLanguageLevel;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

final class ResumeLanguagesRepeater
{
    /**
     * @return Repeater
     */
    public static function make(): Repeater
    {
        return Repeater::make('languages')
            ->label('Мови')
            ->schema([
                TextInput::make('language')
                    ->label('Мова')
                    ->required()
                    ->maxLength(255)
                    ->rule(static fn (Field $component): \Closure => self::uniqueAmongSiblings($component)),
                Select::make('level')
                    ->label('Рівень')
                    ->options(ResumeLanguageLevel::class),
                TextInput::make('level_label')
                    ->label('Підпис рівня')
                    ->maxLength(255),
                Toggle::make('is_visible')
                    ->label('Показувати')
                    ->default(true)
                    ->inline(false),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->reorderableWithDragAndDrop()
            ->addActionLabel('Додати мову')
            ->columnSpanFull();
    }

    /**
     * @param Collection<int, ResumeLanguage> $languages
     *
     * @return list<array<string, mixed>>
     */
    public static function stateFrom(Collection $languages): array
    {
        return array_values($languages
            ->map(static fn (ResumeLanguage $language): array => [
                'language' => $language->language,
                'level' => $language->level?->value,
                'level_label' => $language->level_label,
                'is_visible' => $language->is_visible,
            ])
            ->all());
    }

    /**
     * @param Field $component
     *
     * @return \Closure
     */
    private static function uniqueAmongSiblings(Field $component): \Closure
    {
        return static function (string $attribute, mixed $value, \Closure $fail) use ($component): void {
            $repeater = $component->getParentRepeater();

            if (null === $repeater || !is_string($value) || '' === trim($value)) {
                return;
            }

            $itemKey = (string) str($component->getStatePath())
                ->after($repeater->getStatePath() . '.')
                ->before('.');

            $items = $repeater->getRawState();

            foreach (is_array($items) ? $items : [] as $key => $item) {
                $sibling = is_array($item) ? ($item['language'] ?? null) : null;

                if ((string) $key !== $itemKey && is_string($sibling) && mb_strtolower(trim($sibling)) === mb_strtolower(trim($value))) {
                    $fail('Ця мова вже додана до резюме.');

                    return;
                }
            }
        };
    }
}
