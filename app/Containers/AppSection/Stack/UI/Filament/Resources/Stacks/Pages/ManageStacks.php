<?php

namespace App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Pages;

use App\Containers\AppSection\Stack\Actions\CreateStackAction;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\StackResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManageStacks extends ManageRecords
{
    /**
     * @var string
     */
    protected static string $resource = StackResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Створити технологію')
                ->using(static function (array $data): Stack {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateStackAction::class)->run($attributes);
                }),
        ];
    }
}
