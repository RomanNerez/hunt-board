<?php

namespace App\Containers\AppSection\Profile\Values\Resume;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeLink extends ParentValue
{
    /**
     * @param string $label
     * @param string $url
     * @param string $displayUrl
     */
    public function __construct(
        public string $label,
        public string $url,
        public string $displayUrl,
    ) {
    }
}
