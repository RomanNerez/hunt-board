<?php

namespace App\Containers\AppSection\Resume\Values\Pdf;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeDocumentExperience extends ParentValue
{
    /**
     * @param string $position
     * @param string $company
     * @param string $period
     * @param string|null $location
     * @param string|null $employmentType
     * @param list<ResumeDocumentProject> $projects
     */
    public function __construct(
        public string $position,
        public string $company,
        public string $period,
        public string|null $location,
        public string|null $employmentType,
        public array $projects,
    ) {
    }
}
