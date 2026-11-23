<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\UI\Filament\Clusters\Profile\ProfileCluster;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperienceProjects;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas\WorkExperienceForm;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Tables\WorkExperiencesTable;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class WorkExperienceResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = ProfileWorkExperience::class;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = ProfileCluster::class;

    /**
     * @var string|null
     */
    protected static string|null $slug = 'work-experience';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 12;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Досвід роботи';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'запис досвіду';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Досвід роботи';

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
        return WorkExperienceForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return WorkExperiencesTable::configure($table);
    }

    /**
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageWorkExperiences::route('/'),
            'projects' => ManageWorkExperienceProjects::route('/{record}/projects'),
        ];
    }
}
