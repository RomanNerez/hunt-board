<?php

namespace App\Containers\AppSection\Resume\Values\Pdf;

use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Ship\Parents\Values\Value as ParentValue;

final readonly class ResumeDocument extends ParentValue
{
    /**
     * @param ResumeLocale $locale
     * @param string $fullName
     * @param string|null $headline
     * @param string|null $summary
     * @param list<ResumeDocumentLink> $links
     * @param list<ResumeDocumentExperience> $experiences
     * @param list<ResumeDocumentEducation> $educations
     * @param list<ResumeDocumentLanguage> $languages
     * @param string $fileName
     */
    public function __construct(
        public ResumeLocale $locale,
        public string $fullName,
        public string|null $headline,
        public string|null $summary,
        public array $links,
        public array $experiences,
        public array $educations,
        public array $languages,
        public string $fileName,
    ) {
    }
}
