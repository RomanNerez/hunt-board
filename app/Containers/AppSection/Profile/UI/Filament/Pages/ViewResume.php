<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Pages;

use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\UI\Filament\Clusters\Profile\ProfileCluster;
use App\Containers\AppSection\Profile\UI\Filament\Concerns\InteractsWithDataLocale;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Clusters\Cluster;
use Filament\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

final class ViewResume extends Page
{
    use InteractsWithDataLocale;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = ProfileCluster::class;

    /**
     * @var string|null
     */
    protected static string|null $slug = 'resume';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 50;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Резюме';

    /**
     * @var string|null
     */
    protected static string|null $title = 'Резюме';

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('appSection@profile::resume.preview-frame')
                ->viewData(fn (): array => [
                    'locale' => $this->activeDataLocale()->value,
                    'previewUrl' => $this->hasProfile() ? $this->resumeUrl('preview') : null,
                    'generalUrl' => EditProfileGeneral::getUrl(),
                ]),
        ]);
    }

    /**
     * @return list<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->dataLocaleActionGroup(),
            Action::make('openResume')
                ->label('Відкрити в новій вкладці')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => $this->resumeUrl('preview'))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->hasProfile()),
            Action::make('downloadResume')
                ->label('Завантажити PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(fn (): string => $this->resumeUrl('download'))
                ->visible(fn (): bool => $this->hasProfile()),
        ];
    }

    /**
     * @param string $mode
     *
     * @return string
     */
    private function resumeUrl(string $mode): string
    {
        return route("profile.resume.{$mode}", ['locale' => $this->activeDataLocale()->value]);
    }

    /**
     * @return bool
     */
    private function hasProfile(): bool
    {
        $id = Auth::id();

        return is_numeric($id) && UserProfile::query()->where('user_id', (int) $id)->exists();
    }
}
