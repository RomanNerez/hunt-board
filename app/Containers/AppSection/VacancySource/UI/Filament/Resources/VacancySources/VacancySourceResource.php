<?php

namespace App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Pages\ManageVacancySources;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Schemas\VacancySourceForm;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Tables\VacancySourcesTable;
use App\Ship\Filament\Clusters\Settings\SettingsCluster;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class VacancySourceResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = VacancySource::class;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = SettingsCluster::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 50;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'name';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'джерело вакансії';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Джерела вакансій';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Джерела вакансій';

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
        return VacancySourceForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return VacancySourcesTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageVacancySources::route('/'),
        ];
    }
}
