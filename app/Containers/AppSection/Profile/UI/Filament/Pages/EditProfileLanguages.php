<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Actions\SyncProfileLanguagesAction;
use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EditProfileLanguages extends ProfileFormPage
{
    /**
     * @var string|null
     */
    protected static string|null $slug = 'languages';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 40;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Мови';

    /**
     * @var string|null
     */
    protected static string|null $title = 'Мови';

    /**
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill([
            'languages' => ProfileLanguage::query()
                ->where('user_id', $this->userId())
                ->orderBy('sort')
                ->get()
                ->map(static fn (ProfileLanguage $language): array => [
                    'language' => self::localeValues($language->getTranslations('language')),
                    'level' => $language->level->value,
                    'description' => self::localeValues($language->getTranslations('description')),
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
                Repeater::make('languages')
                    ->hiddenLabel()
                    ->schema([
                        ...$this->translatable('language', fn (string $name, ProfileLocale $locale): TextInput => TextInput::make($name)
                            ->label('Мова')
                            ->maxLength(100)
                            ->datalist(self::languageSuggestions($locale))
                            ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($locale): void {
                                if ($this->isRepeatedLanguage($value, $locale)) {
                                    $fail('Ця мова вже є в списку.');
                                }
                            }), required: true),
                        Select::make('level')
                            ->label('Рівень')
                            ->options(LanguageLevel::class)
                            ->required(),
                        ...$this->translatable('description', static fn (string $name): Textarea => Textarea::make($name)
                            ->label('Опис')
                            ->maxLength(500)
                            ->rows(2)
                            ->autosize()
                            ->columnSpanFull()
                            ->placeholder('Наприклад: вільне володіння для роботи з технічною документацією')),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Додати мову'),
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

        app(SyncProfileLanguagesAction::class)->run($this->userId(), $this->repeaterItems($state, 'languages'));

        $this->notifySaved();
    }

    /**
     * @param ProfileLocale $locale
     *
     * @return list<string>
     */
    private static function languageSuggestions(ProfileLocale $locale): array
    {
        return match ($locale) {
            ProfileLocale::Ukrainian => ['Українська', 'Англійська', 'Польська', 'Німецька', 'Іспанська', 'Французька'],
            ProfileLocale::English => ['Ukrainian', 'English', 'Polish', 'German', 'Spanish', 'French'],
        };
    }

    /**
     * @param mixed $value
     * @param ProfileLocale $locale
     *
     * @return bool
     */
    private function isRepeatedLanguage(mixed $value, ProfileLocale $locale): bool
    {
        if (!is_string($value) || '' === trim($value)) {
            return false;
        }

        $occurrences = 0;

        foreach ($this->repeaterItems($this->data ?? [], 'languages') as $item) {
            $language = is_array($item['language'] ?? null) ? ($item['language'][$locale->value] ?? null) : null;

            if (is_string($language) && $this->normalizeLanguage($language) === $this->normalizeLanguage($value)) {
                ++$occurrences;
            }
        }

        return $occurrences > 1;
    }

    /**
     * @param string $language
     *
     * @return string
     */
    private function normalizeLanguage(string $language): string
    {
        return mb_strtolower(trim($language));
    }
}
