<?php

namespace App\Containers\AppSection\Company\Policies;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class CompanyTypePolicy extends ParentPolicy
{
    /**
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param CompanyType $companyType
     *
     * @return bool
     */
    public function view(User $user, CompanyType $companyType): bool
    {
        return true;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param CompanyType $companyType
     *
     * @return bool
     */
    public function update(User $user, CompanyType $companyType): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param CompanyType $companyType
     *
     * @return bool
     */
    public function delete(User $user, CompanyType $companyType): bool
    {
        return true;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }
}
