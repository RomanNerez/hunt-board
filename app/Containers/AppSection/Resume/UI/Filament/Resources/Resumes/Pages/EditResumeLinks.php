<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\Concerns\EditsResumeSection;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas\ResumeLinksRepeater;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class EditResumeLinks extends EditRecord
{
    use EditsResumeSection;

    /**
     * @var string
     */
    protected static string $resource = ResumeResource::class;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Посилання';

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ResumeLinksRepeater::make(),
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

        return ['links' => ResumeLinksRepeater::stateFrom($resume->load('links')->links)];
    }
}
