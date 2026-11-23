<?php

namespace App\Containers\AppSection\Profile\Values\Resume;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Ship\Parents\Values\Value as ParentValue;

final readonly class Resume extends ParentValue
{
    /**
     * @param ProfileLocale $locale
     * @param string $fullName
     * @param string|null $headline
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $location
     * @param string|null $summary
     * @param list<ResumeLink> $links
     * @param list<ResumeSkillRow> $skillRows
     * @param list<string> $languages
     * @param list<ResumeExperienceCard> $experience
     * @param list<ResumeEducation> $education
     * @param string $fileName
     */
    public function __construct(
        public ProfileLocale $locale,
        public string $fullName,
        public string|null $headline,
        public string|null $email,
        public string|null $phone,
        public string|null $location,
        public string|null $summary,
        public array $links,
        public array $skillRows,
        public array $languages,
        public array $experience,
        public array $education,
        public string $fileName,
    ) {
    }

    /**
     * @return bool
     */
    public function hasSkillsSection(): bool
    {
        return [] !== $this->skillRows || [] !== $this->languages;
    }
}
