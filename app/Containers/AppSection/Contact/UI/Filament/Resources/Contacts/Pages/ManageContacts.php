<?php

namespace App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Pages;

use App\Containers\AppSection\Contact\Actions\CreateContactAction;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

final class ManageContacts extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = ContactResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Створити контакт')
                ->modalWidth(Width::ThreeExtraLarge)
                ->using(static function (array $data): Contact {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateContactAction::class)->run($attributes);
                }),
        ];
    }
}
