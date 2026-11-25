<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Support\ResumeLinkIcon;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocument;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentEducation;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentExperience;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentLanguage;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentLink;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

use function Safe\preg_replace;

final class BuildResumeDocumentTask extends ParentTask
{
    /**
     * @var string
     */
    private const PERIOD_SEPARATOR = ' – ';

    /**
     * @param Resume $resume
     *
     * @return ResumeDocument
     */
    public function run(Resume $resume): ResumeDocument
    {
        $locale = $resume->locale;

        return new ResumeDocument(
            locale: $locale,
            fullName: trim("{$resume->first_name} {$resume->last_name}"),
            headline: $this->filled($resume->headline),
            summary: $this->filled($resume->summary),
            links: $this->links($resume->links),
            experiences: $this->experiences($resume->experiences, $locale),
            educations: $this->educations($resume->educations),
            languages: $this->languages($resume->languages, $locale),
            fileName: $this->fileName($resume),
        );
    }

    /**
     * @param Collection<int, ResumeLink> $links
     *
     * @return list<ResumeDocumentLink>
     */
    private function links(Collection $links): array
    {
        $items = [];

        foreach ($links as $link) {
            if (!$link->is_visible) {
                continue;
            }

            $items[] = new ResumeDocumentLink(
                label: trim($link->label),
                url: $link->url,
                iconSvg: $this->iconSvg($link->icon),
            );
        }

        return $items;
    }

    /**
     * @param Collection<int, ResumeExperience> $experiences
     * @param ResumeLocale $locale
     *
     * @return list<ResumeDocumentExperience>
     */
    private function experiences(Collection $experiences, ResumeLocale $locale): array
    {
        $items = [];

        foreach ($experiences as $experience) {
            if (!$experience->is_visible) {
                continue;
            }

            $items[] = new ResumeDocumentExperience(
                position: $experience->position,
                company: $experience->company,
                period: $this->period($experience->start_date, $experience->end_date, $locale),
                location: $this->filled($experience->location),
                employmentType: $experience->employment_type?->pdfLabel($locale),
                descriptionHtml: $this->html($experience->description),
                stack: $this->stackNames($experience->stacks),
            );
        }

        return $items;
    }

    /**
     * @param Collection<int, ResumeEducation> $educations
     *
     * @return list<ResumeDocumentEducation>
     */
    private function educations(Collection $educations): array
    {
        $items = [];

        foreach ($educations as $education) {
            if (!$education->is_visible) {
                continue;
            }

            $qualification = implode(', ', array_filter(
                [$this->filled($education->degree), $this->filled($education->field_of_study)],
                static fn (string|null $part): bool => null !== $part,
            ));

            $items[] = new ResumeDocumentEducation(
                institution: $education->institution,
                qualification: '' === $qualification ? null : $qualification,
                years: $this->years($education->start_date, $education->end_date),
                descriptionHtml: $this->html($education->description),
            );
        }

        return $items;
    }

    /**
     * @param Collection<int, ResumeLanguage> $languages
     * @param ResumeLocale $locale
     *
     * @return list<ResumeDocumentLanguage>
     */
    private function languages(Collection $languages, ResumeLocale $locale): array
    {
        $items = [];

        foreach ($languages as $language) {
            if (!$language->is_visible) {
                continue;
            }

            $level = $this->filled($language->level_label) ?? $language->level?->pdfLabel($locale);

            $items[] = new ResumeDocumentLanguage(
                line: null === $level ? $language->language : "{$language->language} ({$level})",
            );
        }

        return $items;
    }

    /**
     * @param CarbonImmutable $startDate
     * @param CarbonImmutable|null $endDate
     * @param ResumeLocale $locale
     *
     * @return string
     */
    private function period(CarbonImmutable $startDate, CarbonImmutable|null $endDate, ResumeLocale $locale): string
    {
        $end = null === $endDate
            ? __('appSection@resume::pdf.present', locale: $locale->value)
            : $this->monthYear($endDate, $locale);

        return $this->monthYear($startDate, $locale) . self::PERIOD_SEPARATOR . $end;
    }

    /**
     * @param CarbonImmutable $date
     * @param ResumeLocale $locale
     *
     * @return string
     */
    private function monthYear(CarbonImmutable $date, ResumeLocale $locale): string
    {
        return Str::ucfirst($date->settings(['locale' => $locale->value])->isoFormat('MMM YYYY'));
    }

    /**
     * @param CarbonImmutable|null $startDate
     * @param CarbonImmutable|null $endDate
     *
     * @return string|null
     */
    private function years(CarbonImmutable|null $startDate, CarbonImmutable|null $endDate): string|null
    {
        $years = array_values(array_unique(array_filter(
            [$startDate?->year, $endDate?->year],
            static fn (int|null $year): bool => null !== $year,
        )));

        return [] === $years ? null : implode(self::PERIOD_SEPARATOR, $years);
    }

    /**
     * @param string|null $content
     *
     * @return string|null
     */
    private function html(string|null $content): string|null
    {
        if (null === $this->filled($content)) {
            return null;
        }

        $html = RichContentRenderer::make($content)->toHtml();

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
     * @param string|null $icon
     *
     * @return string|null
     */
    private function iconSvg(string|null $icon): string|null
    {
        if (null === $icon || !array_key_exists($icon, ResumeLinkIcon::all())) {
            return null;
        }

        return svg($icon, 'header__icon')->toHtml();
    }

    /**
     * @param Resume $resume
     *
     * @return string
     */
    private function fileName(Resume $resume): string
    {
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '_', Str::ascii("{$resume->first_name} {$resume->last_name}")), '_');

        return implode('_', array_filter(
            [$slug, 'CV', $resume->locale->fileSuffix()],
            static fn (string $part): bool => '' !== $part,
        )) . '.pdf';
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
