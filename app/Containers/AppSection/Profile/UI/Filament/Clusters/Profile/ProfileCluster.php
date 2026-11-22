<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Clusters\Profile;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

final class ProfileCluster extends Cluster
{
    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    /**
     * @var SubNavigationPosition|null
     */
    protected static SubNavigationPosition|null $subNavigationPosition = SubNavigationPosition::Start;

    /**
     * @var string|null
     */
    protected static string|null $slug = 'profile';

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 90;

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Профіль';

    /**
     * @var string|null
     */
    protected static string|null $clusterBreadcrumb = 'Профіль';
}
