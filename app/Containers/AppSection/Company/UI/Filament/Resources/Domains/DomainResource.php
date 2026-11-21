<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Domains;

use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Pages\ManageDomains;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Schemas\DomainForm;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Tables\DomainsTable;
use App\Ship\Filament\Clusters\Settings\SettingsCluster;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class DomainResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Domain::class;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = SettingsCluster::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 30;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'name';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'Домен';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Домени';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Домени';

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
        return DomainForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return DomainsTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageDomains::route('/'),
        ];
    }
}
