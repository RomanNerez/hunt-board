<?php

namespace App\Containers\AppSection\Profile\Values\Resume;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeSkillRow extends ParentValue
{
    /**
     * @param string $name
     * @param list<string> $items
     */
    public function __construct(
        public string $name,
        public array $items,
    ) {
    }
}
