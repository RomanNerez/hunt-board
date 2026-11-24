<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeEducations;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeExperiences;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeLanguages;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeLinks;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\ListResumes;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas\ResumeForm;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Tables\ResumesTable;
use Filament\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class ResumeResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Resume::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 15;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'last_name';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'Резюме';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Резюме';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Резюме';

    /**
     * @var bool
     */
    protected static bool $hasTitleCaseModelLabel = false;

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return ResumeForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return ResumesTable::configure($table);
    }

    /**
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    /**
     * @param int|string $key
     * @param \Closure|null $modifyQuery
     *
     * @return Model|null
     */
    public static function resolveRecordRouteBinding(int|string $key, \Closure|null $modifyQuery = null): Model|null
    {
        if (!ctype_digit((string) $key)) {
            return null;
        }

        return parent::resolveRecordRouteBinding($key, $modifyQuery);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListResumes::route('/'),
            'edit' => EditResume::route('/{record}/edit'),
            'links' => EditResumeLinks::route('/{record}/links'),
            'experiences' => EditResumeExperiences::route('/{record}/experiences'),
            'educations' => EditResumeEducations::route('/{record}/educations'),
            'languages' => EditResumeLanguages::route('/{record}/languages'),
        ];
    }

    /**
     * @param Page $page
     *
     * @return array<\Filament\Navigation\NavigationItem|\Filament\Navigation\NavigationGroup>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditResume::class,
            EditResumeLinks::class,
            EditResumeExperiences::class,
            EditResumeEducations::class,
            EditResumeLanguages::class,
        ]);
    }
}
