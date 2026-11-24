<?php

namespace App\Containers\AppSection\Vacancy\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class VacancyNotePolicy extends ParentPolicy
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
     * @param VacancyNote $vacancyNote
     *
     * @return bool
     */
    public function view(User $user, VacancyNote $vacancyNote): bool
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
     * @param VacancyNote $vacancyNote
     *
     * @return bool
     */
    public function update(User $user, VacancyNote $vacancyNote): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param VacancyNote $vacancyNote
     *
     * @return bool
     */
    public function delete(User $user, VacancyNote $vacancyNote): bool
    {
        return true;
    }
}
