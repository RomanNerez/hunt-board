<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\UI\WEB\Views;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Enums\ResumeTemplate;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\Values\Resume\Resume;
use App\Containers\AppSection\Profile\Values\Resume\ResumeEducation;
use App\Containers\AppSection\Profile\Values\Resume\ResumeExperienceCard;
use App\Containers\AppSection\Profile\Values\Resume\ResumeLink;
use App\Containers\AppSection\Profile\Values\Resume\ResumeSkillRow;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ClassicResumeTemplateTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testRendersAllSectionsOfFullResume(): void
    {
        $html = $this->render(new Resume(
            locale: ProfileLocale::Ukrainian,
            fullName: 'Роман Нерез',
            headline: 'Senior Full Stack Developer',
            email: 'roman@example.com',
            phone: '+380 00 000 00 00',
            location: 'Запоріжжя, Україна',
            summary: 'Розробляю вебзастосунки.',
            links: [new ResumeLink('GitHub', 'https://github.com/RomanNerez', 'github.com/RomanNerez')],
            skillRows: [new ResumeSkillRow('Backend', ['PHP', 'Laravel']), new ResumeSkillRow('Frontend', ['Vue.js'])],
            languages: ['Англійська (B2 Upper-Intermediate) — вільне читання документації'],
            experience: [
                new ResumeExperienceCard('Lead Developer', 'GiraffeSoftware', 'PippoSAS', 'Січ 2025 – Бер 2026', '<ul><li><p>Перший</p></li><li><p>Другий</p></li></ul>', ['PHP', 'Laravel', 'PostgreSQL']),
                new ResumeExperienceCard('Junior Developer', 'ТОВ Техпром', null, 'Січ 2021 – Сер 2021', null, []),
            ],
            education: [new ResumeEducation('Brain Academy', 'Front-End розробка', 'HTML, CSS', '2019')],
            fileName: 'Roman_Nerez_CV_UA.pdf',
        ));

        $this->assertStringContainsString('<html lang="uk">', $html);
        $this->assertStringContainsString('Роман Нерез', $html);
        $this->assertStringContainsString('Senior Full Stack Developer', $html);
        $this->assertStringContainsString('>Телефон:</span>', $html);
        $this->assertStringContainsString('+380 00 000 00 00', $html);
        $this->assertStringContainsString('>Локація:</span>', $html);
        $this->assertStringContainsString('<a class="header__link" href="https://github.com/RomanNerez">github.com/RomanNerez</a>', $html);
        $this->assertStringContainsString('Розробляю вебзастосунки.', $html);
        $this->assertStringContainsString('Технічні навички', $html);
        $this->assertSeeInOrder(['Backend:', 'PHP, Laravel', 'Frontend:', 'Vue.js', 'Іноземні мови:', 'Англійська (B2 Upper-Intermediate) — вільне читання документації'], $html);
        $this->assertStringContainsString('Досвід роботи', $html);
        $this->assertSeeInOrder(['Lead Developer', '| GiraffeSoftware', 'Січ 2025 – Бер 2026', 'PippoSAS', '<li><p>Перший</p></li>', 'Tech Stack:</strong> PHP, Laravel, PostgreSQL', 'Junior Developer', '| ТОВ Техпром'], $html);
        $this->assertSame(1, substr_count($html, 'Tech Stack:'));
        $this->assertSame(1, substr_count($html, 'class="card__title"'));
        $this->assertStringContainsString('Освіта та курси', $html);
        $this->assertSeeInOrder(['Brain Academy', 'Front-End розробка (2019)', 'HTML, CSS'], $html);
    }

    /**
     * @return void
     */
    public function testHidesEmptyFieldsAndSectionsAndUsesEnglishLabels(): void
    {
        $html = $this->render(new Resume(
            locale: ProfileLocale::English,
            fullName: 'Roman Nerez',
            headline: null,
            email: 'roman@example.com',
            phone: null,
            location: null,
            summary: null,
            links: [],
            skillRows: [],
            languages: [],
            experience: [],
            education: [],
            fileName: 'Roman_Nerez_CV_EN.pdf',
        ));

        $this->assertStringContainsString('<html lang="en">', $html);
        $this->assertStringContainsString('>Email:</span>', $html);
        $this->assertStringNotContainsString('Phone', $html);
        $this->assertStringNotContainsString('Location', $html);
        $this->assertStringNotContainsString('header__headline"', $html);
        $this->assertStringNotContainsString('class="summary"', $html);
        $this->assertStringNotContainsString('Technical Skills', $html);
        $this->assertStringNotContainsString('Work Experience', $html);
        $this->assertStringNotContainsString('Education', $html);
    }

    /**
     * @param Resume $resume
     *
     * @return string
     */
    private function render(Resume $resume): string
    {
        /** @var view-string $view */
        $view = ResumeTemplate::Classic->view();

        return view($view, ['resume' => $resume])->render();
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
