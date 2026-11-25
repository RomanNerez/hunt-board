<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Actions\PreviewResumeAction;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class PreviewResume extends ViewRecord
{
    /**
     * @var string
     */
    private const FRAME_HEIGHT = 'calc(100vh - 13rem)';

    /**
     * @var string
     */
    protected static string $resource = ResumeResource::class;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Перегляд';

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    /**
     * @return SubNavigationPosition
     */
    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('appSection@resume::preview-frame')
                ->viewData(fn (): array => [
                    'previewUrl' => PreviewResumeAction::previewUrl($this->resume()),
                    'height' => self::FRAME_HEIGHT,
                ]),
        ]);
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            PreviewResumeAction::openInNewTabAction($this->resume()),
            PreviewResumeAction::downloadAction($this->resume()),
        ];
    }

    /**
     * @return Resume
     */
    private function resume(): Resume
    {
        /** @var Resume $resume */
        $resume = $this->getRecord();

        return $resume;
    }
}
