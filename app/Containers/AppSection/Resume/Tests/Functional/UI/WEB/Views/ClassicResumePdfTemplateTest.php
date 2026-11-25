<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\UI\WEB\Views;

use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocument;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentEducation;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentExperience;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentLanguage;
use App\Containers\AppSection\Resume\Values\Pdf\ResumeDocumentLink;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ClassicResumePdfTemplateTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testRendersAllSectionsInOrderWithEnglishLabels(): void
    {
        $html = $this->render(new ResumeDocument(
            locale: ResumeLocale::English,
            fullName: 'Roman Nerez',
            headline: 'PHP Developer',
            summary: 'Building web applications.',
            links: [
                new ResumeDocumentLink('GitHub', 'https://github.com/john', '<svg class="header__icon"></svg>'),
                new ResumeDocumentLink('Blog', 'https://john.dev', null),
            ],
            experiences: [
                new ResumeDocumentExperience('Backend Developer', 'MacPaw', 'Jan 2024 – Present', 'Kyiv', 'Full-time', '<ul><li><p>First</p></li></ul>', ['PHP', 'Laravel']),
                new ResumeDocumentExperience('Junior Developer', 'Grammarly', 'Mar 2021 – Dec 2023', null, null, null, []),
            ],
            educations: [new ResumeDocumentEducation('KPI', 'Bachelor, Computer Science', '2015 – 2019', '<p>With honours</p>')],
            languages: [new ResumeDocumentLanguage('English (Fluent)'), new ResumeDocumentLanguage('Ukrainian (Native)')],
            fileName: 'Roman_Nerez_CV_EN.pdf',
        ));

        $this->assertStringContainsString('<html lang="en">', $html);
        $this->assertSeeInOrder(['<a class="header__link" href="https://github.com/john">', '<svg class="header__icon"></svg>', '<span>GitHub</span>', '</a>'], $html);
        $this->assertSeeInOrder(['<a class="header__link" href="https://john.dev">', '<span>Blog</span>', '</a>'], $html);
        $this->assertSame(1, substr_count($this->body($html), '<svg'));
        $this->assertStringNotContainsString('john.dev</', $html);
        $this->assertSeeInOrder([
            'Roman Nerez',
            'PHP Developer',
            'GitHub',
            'Summary',
            'Building web applications.',
            'Work Experience',
            '<div class="card__organization">MacPaw</div>',
            '<div class="card__role">Backend Developer</div>',
            '<span class="card__meta">Kyiv · Full-time</span>',
            '<span class="card__separator">|</span>',
            '<span class="card__period">Jan 2024 – Present</span>',
            '<li><p>First</p></li>',
            'Tech Stack:</strong> PHP, Laravel',
            'Grammarly',
            'Junior Developer',
            'Education',
            'KPI',
            'Bachelor, Computer Science (2015 – 2019)',
            '<p>With honours</p>',
            'Languages',
            'English (Fluent)',
            'Ukrainian (Native)',
        ], $html);
        $this->assertSame(1, substr_count($html, 'Tech Stack:'));
        $this->assertSame(1, substr_count($html, 'class="card__meta"'));
        $this->assertSame(1, substr_count($html, 'class="card__separator"'));
    }

    /**
     * @return void
     */
    public function testResumeWithOnlyNameHidesEmptySectionsAndUsesUkrainianLabels(): void
    {
        $html = $this->render(new ResumeDocument(
            locale: ResumeLocale::Ukrainian,
            fullName: 'Роман Нерез',
            headline: null,
            summary: null,
            links: [],
            experiences: [],
            educations: [],
            languages: [],
            fileName: 'Roman_Nerez_CV_UA.pdf',
        ));

        $this->assertStringContainsString('<html lang="uk">', $html);
        $this->assertStringContainsString('Роман Нерез', $html);

        foreach (['header__headline', 'header__links', 'summary', 'section', 'Про мене', 'Досвід роботи', 'Освіта', 'Мови', 'Work Experience'] as $absent) {
            $this->assertStringNotContainsString($absent, $this->body($html));
        }
    }

    /**
     * @return void
     */
    public function testUkrainianSectionTitles(): void
    {
        $html = $this->render(new ResumeDocument(
            locale: ResumeLocale::Ukrainian,
            fullName: 'Роман Нерез',
            headline: null,
            summary: 'Пишу код.',
            links: [],
            experiences: [new ResumeDocumentExperience('Розробник', 'MacPaw', 'Січ 2024 – Дотепер', null, null, null, ['PHP'])],
            educations: [new ResumeDocumentEducation('КПІ', null, null, null)],
            languages: [new ResumeDocumentLanguage('Англійська (B2 Вище середнього)')],
            fileName: 'Roman_Nerez_CV_UA.pdf',
        ));

        $this->assertSeeInOrder(['Про мене', 'Пишу код.', 'Досвід роботи', 'Січ 2024 – Дотепер', 'Технології:', 'Освіта', 'КПІ', 'Мови', 'Англійська (B2 Вище середнього)'], $html);
        $this->assertStringNotContainsString('education__meta', $this->body($html));
    }

    /**
     * @param ResumeDocument $resume
     *
     * @return string
     */
    private function render(ResumeDocument $resume): string
    {
        return view('appSection@resume::pdf.classic', ['resume' => $resume])->render();
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function body(string $html): string
    {
        return mb_substr($html, (int) mb_strpos($html, '<body>'));
    }

    /**
     * @param list<string> $needles
     * @param string $haystack
     *
     * @return void
     */
    private function assertSeeInOrder(array $needles, string $haystack): void
    {
        $offset = 0;

        foreach ($needles as $needle) {
            $position = mb_strpos($haystack, $needle, $offset);
            $this->assertNotFalse($position, "Failed asserting that [{$needle}] appears after offset {$offset}.");
            $offset = $position + mb_strlen($needle);
        }
    }
}
