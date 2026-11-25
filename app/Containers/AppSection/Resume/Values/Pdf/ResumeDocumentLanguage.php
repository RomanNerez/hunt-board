<?php

namespace App\Containers\AppSection\Resume\Values\Pdf;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeDocumentLanguage extends ParentValue
{
    /**
     * @param string $line
     */
    public function __construct(
        public string $line,
    ) {
    }
}
