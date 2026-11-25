<?php

namespace App\Containers\AppSection\Resume\Values\Pdf;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeDocumentEducation extends ParentValue
{
    /**
     * @param string $institution
     * @param string|null $qualification
     * @param string|null $years
     * @param string|null $descriptionHtml
     */
    public function __construct(
        public string $institution,
        public string|null $qualification,
        public string|null $years,
        public string|null $descriptionHtml,
    ) {
    }
}
