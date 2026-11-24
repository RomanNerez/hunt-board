<?php

namespace App\Containers\AppSection\Contact\Data\Repositories;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Contact
 *
 * @extends ParentRepository<TModel>
 */
final class ContactRepository extends ParentRepository
{
    /**
     * @return class-string<Contact>
     */
    public function model(): string
    {
        return Contact::class;
    }
}
