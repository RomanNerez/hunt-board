<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Actions;

use App\Containers\AppSection\Resume\Actions\CreateResumeAction;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas\ResumeForm;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class CreateResumeModalAction
{
    /**
     * @return CreateAction
     */
    public static function make(): CreateAction
    {
        return CreateAction::make()
            ->label('Створити резюме')
            ->modalHeading('Створити резюме')
            ->schema([
                ResumeForm::firstNameInput(),
                ResumeForm::lastNameInput(),
                ResumeForm::localeField(),
            ])
            ->using(static function (array $data): Model {
                /** @var array<string, mixed> $attributes */
                $attributes = $data;

                return app(CreateResumeAction::class)->run((int) Auth::id(), $attributes);
            })
            ->successRedirectUrl(static fn (Model $record): string => ResumeResource::getUrl('edit', ['record' => $record]));
    }
}
