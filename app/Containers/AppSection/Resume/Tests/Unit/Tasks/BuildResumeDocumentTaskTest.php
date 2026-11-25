<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Enums\ResumeEmploymentType;
use App\Containers\AppSection\Resume\Enums\ResumeLanguageLevel;
use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tasks\BuildResumeDocumentTask;
use App\Containers\AppSection\Resume\Tasks\FindUserResumeTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocument;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentExperience;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentLanguage;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentLink;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentProject;
use App\Containers\AppSection\Stack\Models\Stack;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BuildResumeDocumentTask::class)]
final class BuildResumeDocumentTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testBuildsHeaderAndFileNameFromResume(): void
    {
        $resume = Resume::factory()->createOne([
            'first_name' => 'Roman',
            'last_name' => 'Nerez',
            'headline' => 'PHP Developer',
            'summary' => '  Розробляю вебзастосунки.  ',
        ]);

        $document = $this->build($resume);

        $this->assertSame(ResumeLocale::English, $document->locale);
        $this->assertSame('Roman Nerez', $document->fullName);
        $this->assertSame('PHP Developer', $document->headline);
        $this->assertSame('Розробляю вебзастосунки.', $document->summary);
        $this->assertSame('Roman_Nerez_CV_EN.pdf', $document->fileName);
    }

    /**
     * @return void
     */
    public function testResumeWithOnlyNameHasEmptySections(): void
    {
        $resume = Resume::factory()->createOne(['first_name' => 'Roman', 'last_name' => 'Nerez', 'headline' => null, 'summary' => '']);

        $document = $this->build($resume);

        $this->assertSame('Roman Nerez', $document->fullName);
        $this->assertNull($document->headline);
        $this->assertNull($document->summary);
        $this->assertSame([], $document->links);
        $this->assertSame([], $document->experiences);
        $this->assertSame([], $document->educations);
        $this->assertSame([], $document->languages);
    }

    /**
     * @return void
     */
    public function testCyrillicNameIsTransliteratedInUkrainianFileName(): void
    {
        $resume = Resume::factory()->ukrainian()->createOne(['first_name' => 'Роман', 'last_name' => 'Нерез']);

        $fileName = $this->build($resume)->fileName;

        $this->assertMatchesRegularExpression('/^[A-Za-z_]+_CV_UA\.pdf$/', $fileName);
        $this->assertSame('Roman_Nerez_CV_UA.pdf', $fileName);
    }

    /**
     * @return void
     */
    public function testHiddenRecordsAreSkippedAndOrderIsKept(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'icon' => null, 'label' => 'Telegram', 'url' => 'https://t.me/john', 'sort_order' => 1]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'icon' => 'fab-github', 'label' => 'GitHub', 'url' => 'https://github.com/john', 'sort_order' => 0]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'Hidden', 'sort_order' => 2, 'is_visible' => false]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Grammarly', 'sort_order' => 1]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw', 'sort_order' => 0]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Hidden', 'sort_order' => 2, 'is_visible' => false]);
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Hidden', 'is_visible' => false]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'German', 'is_visible' => false]);

        $document = $this->build($resume);

        $this->assertSame(['GitHub', 'Telegram'], array_map(static fn (ResumeDocumentLink $link): string => $link->label, $document->links));
        $this->assertSame(['https://github.com/john', 'https://t.me/john'], array_map(static fn (ResumeDocumentLink $link): string => $link->url, $document->links));
        $this->assertSame(['MacPaw', 'Grammarly'], array_map(static fn (ResumeDocumentExperience $experience): string => $experience->company, $document->experiences));
        $this->assertSame([], $document->educations);
        $this->assertSame([], $document->languages);
    }

    /**
     * @return void
     */
    public function testLinkIconIsRenderedAsInlineSvgOnlyForKnownIcons(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'icon' => 'fab-github', 'sort_order' => 0]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'icon' => 'unknown-icon', 'sort_order' => 1]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'icon' => null, 'sort_order' => 2]);

        [$github, $unknown, $plain] = $this->build($resume)->links;

        $this->assertNotNull($github->iconSvg);
        $this->assertStringStartsWith('<svg', $github->iconSvg);
        $this->assertStringContainsString('class="header__icon"', $github->iconSvg);
        $this->assertNull($unknown->iconSvg);
        $this->assertNull($plain->iconSvg);
    }

    /**
     * @return void
     */
    public function testEnglishExperienceUsesEnglishLabelsAndKeepsUserContent(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeExperience::factory()->createOne([
            'resume_id' => $resume->id,
            'company' => 'MacPaw',
            'position' => 'Backend Developer',
            'location' => 'Київ',
            'employment_type' => ResumeEmploymentType::FullTime,
            'start_date' => '2024-01-01',
            'end_date' => null,
        ]);

        $item = $this->build($resume)->experiences[0];

        $this->assertSame('Backend Developer', $item->position);
        $this->assertSame('MacPaw', $item->company);
        $this->assertSame('Jan 2024 – Present', $item->period);
        $this->assertSame('Київ', $item->location);
        $this->assertSame('Full-time', $item->employmentType);
        $this->assertSame([], $item->projects);
    }

    /**
     * @return void
     */
    public function testVisibleProjectsKeepOrderDescriptionAndStackNames(): void
    {
        $resume = Resume::factory()->createOne();
        $experience = ResumeExperience::factory()->createOne(['resume_id' => $resume->id]);
        ResumeExperienceProject::factory()->createOne(['resume_experience_id' => $experience->id, 'name' => 'Impacta', 'role' => ' ', 'description' => '<p></p>', 'sort_order' => 1]);
        ResumeExperienceProject::factory()->createOne(['resume_experience_id' => $experience->id, 'name' => 'PippoSAS', 'sort_order' => 2, 'is_visible' => false]);
        $equally = ResumeExperienceProject::factory()->createOne([
            'resume_experience_id' => $experience->id,
            'name' => 'Equally',
            'role' => 'Tech Lead',
            'description' => '<ul><li><p>Перший</p></li></ul>',
            'sort_order' => 0,
        ]);
        $laravel = Stack::factory()->createOne(['name' => 'Laravel']);
        $php = Stack::factory()->createOne(['name' => 'PHP']);
        $equally->stacks()->attach([$laravel->id => ['sort' => 0], $php->id => ['sort' => 1]]);

        [$first, $second] = $projects = $this->build($resume)->experiences[0]->projects;

        $this->assertSame(['Equally', 'Impacta'], array_map(static fn (ResumeDocumentProject $project): string => $project->name, $projects));
        $this->assertSame('Tech Lead', $first->role);
        $this->assertNotNull($first->descriptionHtml);
        $this->assertStringContainsString('<li><p>Перший</p></li>', $first->descriptionHtml);
        $this->assertSame(['Laravel', 'PHP'], $first->stack);
        $this->assertNull($second->role);
        $this->assertNull($second->descriptionHtml);
        $this->assertSame([], $second->stack);
    }

    /**
     * @return void
     */
    public function testUkrainianExperienceUsesUkrainianMonthsAndPresent(): void
    {
        $resume = Resume::factory()->ukrainian()->createOne();
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'start_date' => '2024-01-01', 'end_date' => null, 'employment_type' => ResumeEmploymentType::Contract]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'start_date' => '2021-03-01', 'end_date' => '2023-12-01', 'sort_order' => 1]);

        $experiences = $this->build($resume)->experiences;

        $this->assertStringEndsWith(' – Дотепер', $experiences[0]->period);
        $this->assertSame('Контракт', $experiences[0]->employmentType);
        $this->assertSame('Бер 2021 – Гру 2023', $experiences[1]->period);
    }

    /**
     * @return void
     */
    public function testEducationJoinsQualificationAndYears(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeEducation::factory()->createOne([
            'resume_id' => $resume->id,
            'institution' => 'KPI',
            'degree' => 'Bachelor',
            'field_of_study' => 'Computer Science',
            'start_date' => '2015-09-01',
            'end_date' => '2019-06-01',
            'description' => '<p>Диплом з відзнакою</p>',
            'sort_order' => 0,
        ]);
        ResumeEducation::factory()->createOne([
            'resume_id' => $resume->id,
            'institution' => 'Prometheus',
            'degree' => null,
            'field_of_study' => null,
            'start_date' => null,
            'end_date' => null,
            'description' => '<p></p>',
            'sort_order' => 1,
        ]);

        [$university, $course] = $this->build($resume)->educations;

        $this->assertSame('KPI', $university->institution);
        $this->assertSame('Bachelor, Computer Science', $university->qualification);
        $this->assertSame('2015 – 2019', $university->years);
        $this->assertNotNull($university->descriptionHtml);
        $this->assertStringContainsString('Диплом з відзнакою', $university->descriptionHtml);
        $this->assertNull($course->qualification);
        $this->assertNull($course->years);
        $this->assertNull($course->descriptionHtml);
    }

    /**
     * @return void
     */
    public function testLanguageLineUsesCustomLabelOrLevelInResumeLocale(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'English', 'level' => ResumeLanguageLevel::B2, 'level_label' => 'Fluent', 'sort_order' => 0]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Ukrainian', 'level' => ResumeLanguageLevel::Native, 'level_label' => null, 'sort_order' => 1]);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Polish', 'level' => null, 'level_label' => null, 'sort_order' => 2]);

        $this->assertSame(
            ['English (Fluent)', 'Ukrainian (Native)', 'Polish'],
            array_map(static fn (ResumeDocumentLanguage $language): string => $language->line, $this->build($resume)->languages),
        );
    }

    /**
     * @param Resume $resume
     *
     * @return ResumeDocument
     */
    private function build(Resume $resume): ResumeDocument
    {
        return app(BuildResumeDocumentTask::class)->run(app(FindUserResumeTask::class)->run($resume->user_id, $resume->id));
    }
}
