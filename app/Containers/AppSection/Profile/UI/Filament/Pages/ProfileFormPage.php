<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\UI\Filament\Clusters\Profile\ProfileCluster;
use Filament\Actions\Action;
use Filament\Clusters\Cluster;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
