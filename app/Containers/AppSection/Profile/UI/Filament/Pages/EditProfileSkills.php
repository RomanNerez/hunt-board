<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Actions\SyncProfileSkillGroupsAction;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Stack\UI\Filament\Components\StackSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EditProfileSkills extends ProfileFormPage
{
    /**
     * @var string|null
     */
    protected static string|null $slug = 'skills';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 15;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Навички';

    /**
     * @var string|null
     */
    protected static string|null $title = 'Навички';

    /**
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill([
            'groups' => ProfileSkillGroup::query()
                ->with('stacks')
                ->where('user_id', $this->userId())
                ->orderBy('sort')
                ->get()
                ->map(static fn (ProfileSkillGroup $group): array => [
                    'name' => self::localeValues($group->getTranslations('name')),
                    'stacks' => $group->stacks->modelKeys(),
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
                Repeater::make('groups')
                    ->hiddenLabel()
                    ->schema([
                        ...$this->translatable('name', static fn (string $name, ProfileLocale $locale): TextInput => TextInput::make($name)
                            ->label('Назва групи')
                            ->maxLength(100)
                            ->datalist(match ($locale) {
                                ProfileLocale::Ukrainian => ['Backend', 'Frontend', 'Бази даних', 'DevOps', 'Інструменти'],
                                ProfileLocale::English => ['Backend', 'Frontend', 'Databases', 'DevOps', 'Tools'],
                            }), required: true),
                        StackSelect::make('stacks', 'Технології')
                            ->required(),
                    ])
                    ->defaultItems(0)
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->addActionLabel('Додати групу')
                    ->itemLabel(fn (array $state): string|null => self::translationWithFallback($state['name'] ?? null, $this->activeDataLocale())),
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

        app(SyncProfileSkillGroupsAction::class)->run($this->userId(), $this->repeaterItems($state, 'groups'));

        $this->notifySaved();
    }
}
