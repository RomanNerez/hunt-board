<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ProfileSkillGroup
 *
 * @extends ParentRepository<TModel>
 */
final class ProfileSkillGroupRepository extends ParentRepository
{
    /**
     * @return class-string<ProfileSkillGroup>
     */
    public function model(): string
    {
        return ProfileSkillGroup::class;
    }
}
