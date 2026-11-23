<?php

namespace App\Containers\AppSection\Profile\Values\Resume;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeExperienceCard extends ParentValue
{
    /**
     * @param string $role
     * @param string $organization
     * @param string|null $title
     * @param string $period
     * @param string|null $descriptionHtml
     * @param list<string> $stack
     */
    public function __construct(
        public string $role,
        public string $organization,
        public string|null $title,
        public string $period,
        public string|null $descriptionHtml,
        public array $stack,
    ) {
    }
}
