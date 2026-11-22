<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Actions\SyncProfileSkillGroupsAction;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
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
                ->where('user_id', $this->userId())
                ->orderBy('sort')
                ->get()
                ->map(static fn (ProfileSkillGroup $group): array => $group->only(['name', 'skills']))
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
                        TextInput::make('name')
                            ->label('Назва групи')
                            ->required()
                            ->maxLength(100)
                            ->datalist(['Backend', 'Frontend', 'Бази даних', 'DevOps', 'Інструменти']),
                        TagsInput::make('skills')
                            ->label('Технології')
                            ->required()
                            ->reorderable()
                            ->nestedRecursiveRules(['max:100'])
                            ->placeholder('Введіть технологію й натисніть Enter'),
                    ])
                    ->defaultItems(0)
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->addActionLabel('Додати групу')
                    ->itemLabel(static fn (array $state): string|null => is_string($state['name'] ?? null) ? $state['name'] : null),
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
