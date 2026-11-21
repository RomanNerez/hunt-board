<?php

namespace App\Containers\AppSection\Status\Policies;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class StatusPolicy extends ParentPolicy
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
     * @param Status $status
     *
     * @return bool
     */
    public function view(User $user, Status $status): bool
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
     * @param Status $status
     *
     * @return bool
     */
    public function update(User $user, Status $status): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param Status $status
     *
     * @return bool
     */
    public function delete(User $user, Status $status): bool
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
