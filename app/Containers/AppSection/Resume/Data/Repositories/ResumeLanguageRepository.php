<?php

namespace App\Containers\AppSection\Resume\Data\Repositories;

use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ResumeLanguage
 *
 * @extends ParentRepository<TModel>
 */
final class ResumeLanguageRepository extends ParentRepository
{
    /**
     * @return class-string<ResumeLanguage>
     */
    public function model(): string
    {
        return ResumeLanguage::class;
    }
}
