<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Actions\DeleteResumeAction;
use App\Containers\AppSection\Resume\Actions\UpdateResumeAction;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditResume extends EditRecord
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
            DeleteAction::make()
                ->modalHeading('Видалити резюме')
                ->using(static fn (Resume $record): bool => app(DeleteResumeAction::class)->run($record->id)),
        ];
    }

    /**
     * @param Model $record
     * @param array<string, mixed> $data
     *
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $data;

        /** @var int $id */
        $id = $record->getKey();

        return app(UpdateResumeAction::class)->run($id, $attributes);
    }

    /**
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return ResumeResource::getUrl('index');
    }
}
