<?php

namespace App\Containers\AppSection\Profile\Policies;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class ProfileProjectPolicy extends ParentPolicy
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
     * @param ProfileProject $project
     *
     * @return bool
     */
    public function view(User $user, ProfileProject $project): bool
    {
        return $this->owns($user, $project);
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
     * @param ProfileProject $project
     *
     * @return bool
     */
    public function update(User $user, ProfileProject $project): bool
    {
        return $this->owns($user, $project);
    }

    /**
     * @param User $user
     * @param ProfileProject $project
     *
     * @return bool
     */
    public function delete(User $user, ProfileProject $project): bool
    {
        return $this->owns($user, $project);
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

    /**
     * @param User $user
     * @param ProfileProject $project
     *
     * @return bool
     */
    private function owns(User $user, ProfileProject $project): bool
    {
        return $user->id === $project->loadMissing('workExperience')->workExperience->user_id;
    }
}
