<?php

namespace App\Containers\AppSection\Resume\Values\Pdf;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeDocumentLink extends ParentValue
{
    /**
     * @param string $label
     * @param string $url
     * @param string|null $iconSvg
     */
    public function __construct(
        public string $label,
        public string $url,
        public string|null $iconSvg,
    ) {
    }
}
