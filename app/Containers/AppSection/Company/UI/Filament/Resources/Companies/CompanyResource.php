<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Companies;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Pages\ManageCompanies;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Tables\CompaniesTable;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class CompanyResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Company::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

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
    protected static string|null $modelLabel = 'Компанія';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Компанії';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Компанії';

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
        return CompanyForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageCompanies::route('/'),
        ];
    }
}
