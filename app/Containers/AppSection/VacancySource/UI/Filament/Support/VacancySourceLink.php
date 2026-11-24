<?php

namespace App\Containers\AppSection\VacancySource\UI\Filament\Support;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class VacancySourceLink
{
    /**
     * @param VacancySource $source
     *
     * @return string|null
     */
    public static function url(VacancySource $source): string|null
    {
        /** @var Pivot $pivot */
        $pivot = $source->getRelation('pivot');
        $url = $pivot->getAttribute('url');

        return is_string($url) ? $url : null;
    }
}
