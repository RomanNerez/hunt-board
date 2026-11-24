<?php

namespace App\Containers\AppSection\VacancySource\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class VacancySourcePolicy extends ParentPolicy
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
     * @param VacancySource $vacancySource
     *
     * @return bool
     */
    public function view(User $user, VacancySource $vacancySource): bool
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
     * @param VacancySource $vacancySource
     *
     * @return bool
     */
    public function update(User $user, VacancySource $vacancySource): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param VacancySource $vacancySource
     *
     * @return bool
     */
    public function delete(User $user, VacancySource $vacancySource): bool
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
