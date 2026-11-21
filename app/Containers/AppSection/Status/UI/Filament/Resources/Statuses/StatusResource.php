<?php

namespace App\Containers\AppSection\Status\UI\Filament\Resources\Statuses;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Pages\ManageStatuses;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Schemas\StatusForm;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Tables\StatusesTable;
use App\Ship\Filament\Clusters\Settings\SettingsCluster;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class StatusResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Status::class;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = SettingsCluster::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'name';

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return StatusForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return StatusesTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageStatuses::route('/'),
        ];
    }
}
