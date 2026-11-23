<?php

namespace App\Containers\AppSection\Profile\Values\Resume;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeEducation extends ParentValue
{
    /**
     * @param string $heading
     * @param string|null $subtitle
     * @param string|null $description
     * @param string|null $years
     */
    public function __construct(
        public string $heading,
        public string|null $subtitle,
        public string|null $description,
        public string|null $years,
    ) {
    }
}
