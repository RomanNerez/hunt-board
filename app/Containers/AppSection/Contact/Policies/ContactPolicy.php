<?php

namespace App\Containers\AppSection\Contact\Policies;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class ContactPolicy extends ParentPolicy
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
     * @param Contact $contact
     *
     * @return bool
     */
    public function view(User $user, Contact $contact): bool
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
     * @param Contact $contact
     *
     * @return bool
     */
    public function update(User $user, Contact $contact): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param Contact $contact
     *
     * @return bool
     */
    public function delete(User $user, Contact $contact): bool
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
