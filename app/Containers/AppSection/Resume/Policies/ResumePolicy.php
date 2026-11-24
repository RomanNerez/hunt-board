<?php

namespace App\Containers\AppSection\Resume\Policies;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class ResumePolicy extends ParentPolicy
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
     * @param Resume $resume
     *
     * @return bool
     */
    public function view(User $user, Resume $resume): bool
    {
        return $resume->user_id === $user->id;
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
     * @param Resume $resume
     *
     * @return bool
     */
    public function update(User $user, Resume $resume): bool
    {
        return $resume->user_id === $user->id;
    }

    /**
     * @param User $user
     * @param Resume $resume
     *
     * @return bool
     */
    public function delete(User $user, Resume $resume): bool
    {
        return $resume->user_id === $user->id;
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
