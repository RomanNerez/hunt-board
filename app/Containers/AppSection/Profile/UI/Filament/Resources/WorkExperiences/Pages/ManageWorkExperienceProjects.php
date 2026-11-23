<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages;

use App\Containers\AppSection\Profile\Actions\CreateProfileProjectAction;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\UI\Filament\Concerns\InteractsWithDataLocale;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas\ProjectForm;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Tables\ProjectsTable;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\WorkExperienceResource;
use App\Containers\AppSection\Profile\UI\Filament\Support\MountedActionValidation;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

/**
 * @extends ManageRelatedRecords<ProfileWorkExperience>
 */
final class ManageWorkExperienceProjects extends ManageRelatedRecords
{
    use InteractsWithDataLocale;

    /**
     * @var string
     */
    protected static string $resource = WorkExperienceResource::class;

    /**
     * @var string
     */
    protected static string $relationship = 'projects';

    /**
     * @var string|null
     */
    protected static string|null $relationshipTitle = 'Проєкти';

    /**
     * @param array<string, mixed> $parameters
     *
     * @return bool
     */
    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        return $record instanceof ProfileWorkExperience
            && parent::canAccess($parameters)
            && Gate::allows('update', $record);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return "Проєкти: {$this->getRecordTitle()}";
    }

    /**
     * @return string
     */
    public function getRecordTitle(): string
    {
        return self::translationWithFallback($this->getOwnerRecord()->getTranslations('company_name'), $this->activeDataLocale()) ?? '';
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            ...$this->getResourceBreadcrumbs(),
            $this->getRecordTitle(),
            $this->getBreadcrumb(),
        ];
    }

    /**
     * @return array<NavigationItem|NavigationGroup>
     */
    public function getSubNavigation(): array
    {
        $cluster = static::getCluster();

        if (null === $cluster || !$cluster::shouldRegisterSubNavigation()) {
            return [];
        }

        /** @var array<class-string<Page>> $components */
        $components = $cluster::getClusteredComponents();

        return $this->generateNavigationItems($components);
    }

    /**
     * @return ProfileWorkExperience
     */
    public function getOwnerRecord(): ProfileWorkExperience
    {
        /** @var ProfileWorkExperience $record */
        $record = $this->getRecord();

        return $record;
    }

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->dataLocaleActionGroup(),
            CreateAction::make()
                ->label('Додати проєкт')
                ->modalHeading('Додати проєкт')
                ->slideOver()
                ->using(function (array $data, Action $action): ProfileProject {
                    /** @var array<string, mixed> $attributes */
                    $attributes = $data;

                    return MountedActionValidation::run($action, fn (): ProfileProject => app(CreateProfileProjectAction::class)->run(
                        $this->getOwnerRecord()->id,
                        $attributes,
                    ));
                }),
        ];
    }
}
