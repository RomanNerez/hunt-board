<?php

namespace App\Ship\Filament\Clusters\Settings;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

final class SettingsCluster extends Cluster
{
    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * @var SubNavigationPosition|null
     */
    protected static SubNavigationPosition|null $subNavigationPosition = SubNavigationPosition::Start;

    /**
     * @var string|null
     */
    protected static string|null $slug = 'settings';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 100;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Налаштування';

    /**
     * @var string|null
     */
    protected static string|null $clusterBreadcrumb = 'Налаштування';
}
