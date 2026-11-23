<?php

namespace App\Containers\AppSection\Stack\Policies;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class StackPolicy extends ParentPolicy
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
     * @param Stack $stack
     *
     * @return bool
     */
    public function view(User $user, Stack $stack): bool
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
     * @param Stack $stack
     *
     * @return bool
     */
    public function update(User $user, Stack $stack): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param Stack $stack
     *
     * @return bool
     */
    public function delete(User $user, Stack $stack): bool
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
