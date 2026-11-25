<?php

namespace App\Containers\AppSection\Resume\Values\Pdf;

use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeDocumentProject extends ParentValue
{
    /**
     * @param string $name
     * @param string|null $role
     * @param string|null $descriptionHtml
     * @param list<string> $stack
     */
    public function __construct(
        public string $name,
        public string|null $role,
        public string|null $descriptionHtml,
        public array $stack,
    ) {
    }
}
