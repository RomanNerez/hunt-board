<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages;

use App\Containers\AppSection\Profile\Actions\CreateProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\UI\Filament\Concerns\InteractsWithDataLocale;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\WorkExperienceResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

final class ManageWorkExperiences extends ManageRecords
{
    use InteractsWithDataLocale;

    /**
     * @var string
     */
    protected static string $resource = WorkExperienceResource::class;

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->dataLocaleActionGroup(),
            CreateAction::make()
                ->modalHeading('Додати досвід роботи')
                ->using(function (array $data): ProfileWorkExperience {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return app(CreateProfileWorkExperienceAction::class)->run($this->userId(), $attributes);
                }),
        ];
    }

    /**
     * @return int
     */
    private function userId(): int
    {
        $id = Auth::id();

        return is_numeric($id) ? (int) $id : 0;
    }
}
