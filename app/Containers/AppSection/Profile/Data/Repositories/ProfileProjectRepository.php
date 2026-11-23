<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ProfileProject
 *
 * @extends ParentRepository<TModel>
 */
final class ProfileProjectRepository extends ParentRepository
{
    /**
     * @return class-string<ProfileProject>
     */
    public function model(): string
    {
        return ProfileProject::class;
    }
}
