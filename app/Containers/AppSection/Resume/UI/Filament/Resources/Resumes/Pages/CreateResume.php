<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Actions\CreateResumeAction;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class CreateResume extends CreateRecord
{
    /**
     * @var string
     */
    protected static string $resource = ResumeResource::class;

    /**
     * @var bool
     */
    protected static bool $canCreateAnother = false;

    /**
     * @param array<string, mixed> $data
     *
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $data;

        return app(CreateResumeAction::class)->run((int) Auth::id(), $attributes);
    }

    /**
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return ResumeResource::getUrl('index');
    }
}
