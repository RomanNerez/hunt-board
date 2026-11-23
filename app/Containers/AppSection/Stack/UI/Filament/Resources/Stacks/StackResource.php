<?php

namespace App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Pages\ManageStacks;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Schemas\StackForm;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Tables\StacksTable;
use App\Ship\Filament\Clusters\Settings\SettingsCluster;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class StackResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Stack::class;

    /**
     * @var class-string<Cluster>|null
     */
    protected static string|null $cluster = SettingsCluster::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 40;

    /**
     * @var string|null
     */
    protected static string|null $slug = 'stacks';

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'name';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'Технологія';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Технології';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Технології';

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
        return StackForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return StacksTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageStacks::route('/'),
        ];
    }
}
