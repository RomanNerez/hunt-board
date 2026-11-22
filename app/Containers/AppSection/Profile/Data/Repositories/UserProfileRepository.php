<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of UserProfile
 *
 * @extends ParentRepository<TModel>
 */
final class UserProfileRepository extends ParentRepository
{
    /**
     * @return class-string<UserProfile>
     */
    public function model(): string
    {
        return UserProfile::class;
    }
}
