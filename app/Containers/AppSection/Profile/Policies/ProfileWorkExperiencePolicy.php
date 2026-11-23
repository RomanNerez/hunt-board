<?php

namespace App\Containers\AppSection\Profile\Policies;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class ProfileWorkExperiencePolicy extends ParentPolicy
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
     * @param ProfileWorkExperience $experience
     *
     * @return bool
     */
    public function view(User $user, ProfileWorkExperience $experience): bool
    {
        return $this->owns($user, $experience);
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
     * @param ProfileWorkExperience $experience
     *
     * @return bool
     */
    public function update(User $user, ProfileWorkExperience $experience): bool
    {
        return $this->owns($user, $experience);
    }

    /**
     * @param User $user
     * @param ProfileWorkExperience $experience
     *
     * @return bool
     */
    public function delete(User $user, ProfileWorkExperience $experience): bool
    {
        return $this->owns($user, $experience);
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
     * @param ProfileWorkExperience $experience
     *
     * @return bool
     */
    private function owns(User $user, ProfileWorkExperience $experience): bool
    {
        return $user->id === $experience->user_id;
    }
}
