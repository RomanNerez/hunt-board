<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Actions\DeleteResumeAction;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\Concerns\EditsResumeSection;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

final class EditResume extends EditRecord
{
    use EditsResumeSection;

    /**
     * @var string
     */
    protected static string $resource = ResumeResource::class;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Загальне';

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Видалити резюме')
                ->using(static fn (Resume $record): bool => app(DeleteResumeAction::class)->run($record->id)),
        ];
    }
}
