<?php

namespace App\Containers\AppSection\Profile\Tasks;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Exceptions\ResumeNotAvailable;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Values\Resume\Resume;
use App\Containers\AppSection\Profile\Values\Resume\ResumeEducation;
use App\Containers\AppSection\Profile\Values\Resume\ResumeExperienceCard;
use App\Containers\AppSection\Profile\Values\Resume\ResumeLink;
use App\Containers\AppSection\Profile\Values\Resume\ResumeSkillRow;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

use function Safe\preg_replace;

final class BuildResumeTask extends ParentTask
{
    /**
     * @var list<string>
     */
    private const RELATIONS = [
        'links',
        'skillGroups.stacks',
        'languages',
        'educations',
        'workExperiences.projects.stacks',
    ];

    /**
     * @var string
     */
    private const PERIOD_SEPARATOR = ' – ';

    /**
     * @param int $userId
     * @param ProfileLocale $locale
     *
     * @return Resume
     *
     * @throws ResumeNotAvailable
     */
    public function run(int $userId, ProfileLocale $locale): Resume
    {
        $profile = UserProfile::query()
            ->with(self::RELATIONS)
            ->where('user_id', $userId)
            ->first();

        if (!$profile instanceof UserProfile) {
            throw ResumeNotAvailable::becauseProfileIsMissing();
        }

        $firstName = $this->translate($profile, 'first_name', $locale) ?? '';
        $lastName = $this->translate($profile, 'last_name', $locale) ?? '';

        return new Resume(
            locale: $locale,
            fullName: trim("{$firstName} {$lastName}"),
            headline: $this->translate($profile, 'headline', $locale),
            email: $this->filled($profile->email),
            phone: $this->filled($profile->phone),
            location: $this->translate($profile, 'location', $locale),
            summary: $this->translate($profile, 'summary', $locale),
            links: $this->links($profile->links, $locale),
            skillRows: $this->skillRows($profile->skillGroups, $locale),
            languages: $this->languages($profile->languages, $locale),
            experience: $this->experience($profile->workExperiences, $locale),
            education: $this->education($profile->educations, $locale),
            fileName: $this->fileName($profile, $locale),
        );
    }

    /**
     * @param Collection<int, ProfileLink> $links
     * @param ProfileLocale $locale
     *
     * @return list<ResumeLink>
     */
    private function links(Collection $links, ProfileLocale $locale): array
    {
        $items = [];

        foreach ($links as $link) {
            $url = $this->translate($link, 'url', $locale);

            if (null === $url) {
                continue;
            }

            $items[] = new ResumeLink(
                label: $this->translate($link, 'label', $locale) ?? '',
                url: $url,
                displayUrl: $this->displayUrl($url),
            );
        }

        return $items;
    }

    /**
     * @param Collection<int, ProfileSkillGroup> $groups
     * @param ProfileLocale $locale
     *
     * @return list<ResumeSkillRow>
     */
    private function skillRows(Collection $groups, ProfileLocale $locale): array
    {
        $rows = [];

        foreach ($groups as $group) {
            $items = $this->stackNames($group->stacks);

            if ([] === $items) {
                continue;
            }

            $rows[] = new ResumeSkillRow(
                name: $this->translate($group, 'name', $locale) ?? '',
                items: $items,
            );
        }

        return $rows;
    }

    /**
     * @param Collection<int, ProfileLanguage> $languages
     * @param ProfileLocale $locale
     *
     * @return list<string>
     */
    private function languages(Collection $languages, ProfileLocale $locale): array
    {
        $items = [];

        foreach ($languages as $language) {
            $line = sprintf(
                '%s (%s)',
                $this->translate($language, 'language', $locale) ?? '',
                $language->level->resumeLabel($locale),
            );
            $description = $this->translate($language, 'description', $locale);

            $items[] = null === $description ? $line : "{$line} — {$description}";
        }

        return $items;
    }

    /**
     * @param Collection<int, ProfileWorkExperience> $experiences
     * @param ProfileLocale $locale
     *
     * @return list<ResumeExperienceCard>
     */
    private function experience(Collection $experiences, ProfileLocale $locale): array
    {
        $cards = [];

        foreach ($experiences as $experience) {
            $role = $this->translate($experience, 'role', $locale) ?? '';
            $organization = WorkExperienceType::FREELANCE === $experience->type
                ? __('appSection@profile::resume.freelance', locale: $locale->value)
                : $this->translate($experience, 'company_name', $locale) ?? '';

            if ($experience->projects->isEmpty()) {
                $cards[] = new ResumeExperienceCard(
                    role: $role,
                    organization: $organization,
                    title: null,
                    period: $this->period($experience->started_on, $experience->finished_on, $locale),
                    descriptionHtml: null,
                    stack: [],
                );

                continue;
            }

            foreach ($experience->projects as $project) {
                $cards[] = new ResumeExperienceCard(
                    role: $role,
                    organization: $organization,
                    title: $this->translate($project, 'name', $locale),
                    period: $this->period($project->started_on, $project->finished_on, $locale),
                    descriptionHtml: $this->descriptionHtml($project, $locale),
                    stack: $this->stackNames($project->stacks),
                );
            }
        }

        return $cards;
    }

    /**
     * @param Collection<int, ProfileEducation> $educations
     * @param ProfileLocale $locale
     *
     * @return list<ResumeEducation>
     */
    private function education(Collection $educations, ProfileLocale $locale): array
    {
        $items = [];

        foreach ($educations as $education) {
            $title = $this->translate($education, 'title', $locale) ?? '';
            $institution = $this->translate($education, 'institution', $locale);

            $items[] = new ResumeEducation(
                heading: $institution ?? $title,
                subtitle: null === $institution ? null : $title,
                description: $this->translate($education, 'description', $locale),
                years: $this->years($education->started_on, $education->finished_on),
            );
        }

        return $items;
    }

    /**
     * @param CarbonImmutable $startedOn
     * @param CarbonImmutable|null $finishedOn
     * @param ProfileLocale $locale
     *
     * @return string
     */
    private function period(CarbonImmutable $startedOn, CarbonImmutable|null $finishedOn, ProfileLocale $locale): string
    {
        $finished = null === $finishedOn
            ? __('appSection@profile::resume.present', locale: $locale->value)
            : $this->monthYear($finishedOn, $locale);

        return $this->monthYear($startedOn, $locale) . self::PERIOD_SEPARATOR . $finished;
    }

    /**
     * @param CarbonImmutable $date
     * @param ProfileLocale $locale
     *
     * @return string
     */
    private function monthYear(CarbonImmutable $date, ProfileLocale $locale): string
    {
        return Str::ucfirst($date->settings(['locale' => $locale->value])->isoFormat('MMM YYYY'));
    }

    /**
     * @param CarbonImmutable|null $startedOn
     * @param CarbonImmutable|null $finishedOn
     *
     * @return string|null
     */
    private function years(CarbonImmutable|null $startedOn, CarbonImmutable|null $finishedOn): string|null
    {
        $years = array_values(array_unique(array_filter([$startedOn?->year, $finishedOn?->year], static fn (int|null $year): bool => null !== $year)));

        return [] === $years ? null : implode(self::PERIOD_SEPARATOR, $years);
    }

    /**
     * @param ProfileProject $project
     * @param ProfileLocale $locale
     *
     * @return string|null
     */
    private function descriptionHtml(ProfileProject $project, ProfileLocale $locale): string|null
    {
        $description = $this->translate($project, 'description', $locale);

        if (null === $description) {
            return null;
        }

        $html = RichContentRenderer::make($description)->toHtml();

        return '' === trim(strip_tags($html)) ? null : $html;
    }

    /**
     * @param Collection<int, Stack> $stacks
     *
     * @return list<string>
     */
    private function stackNames(Collection $stacks): array
    {
        return array_values($stacks->map(static fn (Stack $stack): string => $stack->name)->all());
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function displayUrl(string $url): string
    {
        return rtrim(preg_replace('~^(?:[a-z][a-z0-9+.-]*://)?(?:www\.)?~i', '', $url), '/');
    }

    /**
     * @param UserProfile $profile
     * @param ProfileLocale $locale
     *
     * @return string
     */
    private function fileName(UserProfile $profile, ProfileLocale $locale): string
    {
        $name = implode(' ', array_filter(
            [
                $this->translate($profile, 'first_name', ProfileLocale::English),
                $this->translate($profile, 'last_name', ProfileLocale::English),
            ],
            static fn (string|null $part): bool => null !== $part,
        ));
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '_', Str::ascii($name)), '_');

        return implode('_', array_filter([$slug, 'CV', $locale->fileSuffix()], static fn (string $part): bool => '' !== $part)) . '.pdf';
    }

    /**
     * @param UserProfile|ProfileLink|ProfileSkillGroup|ProfileLanguage|ProfileWorkExperience|ProfileProject|ProfileEducation $model
     * @param string $field
     * @param ProfileLocale $locale
     *
     * @return string|null
     */
    private function translate(
        UserProfile|ProfileLink|ProfileSkillGroup|ProfileLanguage|ProfileWorkExperience|ProfileProject|ProfileEducation $model,
        string $field,
        ProfileLocale $locale,
    ): string|null {
        foreach ([$locale, ProfileLocale::primary()] as $candidate) {
            /** @var mixed $value */
            $value = $model->getTranslation($field, $candidate->value, false);
            $text = $this->filled(is_string($value) ? $value : null);

            if (null !== $text) {
                return $text;
            }
        }

        return null;
    }

    /**
     * @param string|null $value
     *
     * @return string|null
     */
    private function filled(string|null $value): string|null
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}
