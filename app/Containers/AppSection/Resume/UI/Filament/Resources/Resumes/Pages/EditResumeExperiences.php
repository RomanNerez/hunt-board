<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\Concerns\EditsResumeSection;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas\ResumeExperiencesRepeater;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class EditResumeExperiences extends EditRecord
{
    use EditsResumeSection;

    /**
     * @var string
     */
    protected static string $resource = ResumeResource::class;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Досвід';

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ResumeExperiencesRepeater::make(),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Resume $resume */
        $resume = $this->getRecord();

        return ['experiences' => ResumeExperiencesRepeater::stateFrom($resume->load('experiences.projects.stacks')->experiences)];
    }
}
