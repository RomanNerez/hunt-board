<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\Concerns;

use App\Containers\AppSection\Resume\Actions\UpdateResumeAction;
use Filament\Pages\Enums\SubNavigationPosition;
use Illuminate\Database\Eloquent\Model;

trait EditsResumeSection
{
    /**
     * @return SubNavigationPosition
     */
    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    /**
     * @param Model $record
     * @param array<string, mixed> $data
     *
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $data;

        /** @var int $id */
        $id = $record->getKey();

        return app(UpdateResumeAction::class)->run($id, $attributes);
    }
}
