<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Pages\ManageCompanyTypes;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Schemas\CompanyTypeForm;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Tables\CompanyTypesTable;
use App\Ship\Filament\Clusters\Settings\SettingsCluster;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class CompanyTypeResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = CompanyType::class;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = SettingsCluster::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 20;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'name';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'Тип компанії';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Типи компаній';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Типи компаній';

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
        return CompanyTypeForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return CompanyTypesTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageCompanyTypes::route('/'),
        ];
    }
}
