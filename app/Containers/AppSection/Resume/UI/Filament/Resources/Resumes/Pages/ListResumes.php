<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListResumes extends ListRecords
{
    /**
     * @var string
     */
    protected static string $resource = ResumeResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Створити резюме')
                ->url(static fn (): string => ResumeResource::getUrl('create')),
        ];
    }
}
