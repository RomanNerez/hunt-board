<?php

namespace App\Containers\AppSection\Company\Policies;

use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class DomainPolicy extends ParentPolicy
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
     * @param Domain $domain
     *
     * @return bool
     */
    public function view(User $user, Domain $domain): bool
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
     * @param Domain $domain
     *
     * @return bool
     */
    public function update(User $user, Domain $domain): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param Domain $domain
     *
     * @return bool
     */
    public function delete(User $user, Domain $domain): bool
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
