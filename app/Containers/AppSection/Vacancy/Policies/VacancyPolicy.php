<?php

namespace App\Containers\AppSection\Vacancy\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class VacancyPolicy extends ParentPolicy
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
     * @param Vacancy $vacancy
     *
     * @return bool
     */
    public function view(User $user, Vacancy $vacancy): bool
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
     * @param Vacancy $vacancy
     *
     * @return bool
     */
    public function update(User $user, Vacancy $vacancy): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param Vacancy $vacancy
     *
     * @return bool
     */
    public function delete(User $user, Vacancy $vacancy): bool
    {
        return true;
    }
}
