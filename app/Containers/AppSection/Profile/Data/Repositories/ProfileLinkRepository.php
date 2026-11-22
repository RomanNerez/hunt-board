<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ProfileLink
 *
 * @extends ParentRepository<TModel>
 */
final class ProfileLinkRepository extends ParentRepository
{
    /**
     * @return class-string<ProfileLink>
     */
    public function model(): string
    {
        return ProfileLink::class;
    }
}
