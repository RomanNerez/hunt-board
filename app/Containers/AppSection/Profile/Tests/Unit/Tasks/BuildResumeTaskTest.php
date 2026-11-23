<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Exceptions\ResumeNotAvailable;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tasks\BuildResumeTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Profile\Values\Resume\Resume;
use App\Containers\AppSection\Profile\Values\Resume\ResumeExperienceCard;
use App\Containers\AppSection\Profile\Values\Resume\ResumeSkillRow;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BuildResumeTask::class)]
final class BuildResumeTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHeaderContainsProfileContactsAndLinks(): void
    {
        $profile = $this->profile([
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'headline' => 'Senior Full Stack Developer',
            'email' => 'roman@example.com',
            'phone' => '+380 00 000 00 00',
            'location' => 'Запоріжжя, Україна',
            'summary' => 'Розробляю вебзастосунки.',
        ]);
        ProfileLink::factory()->createOne(['user_id' => $profile->user_id, 'label' => 'GitHub', 'url' => 'https://github.com/RomanNerez/']);
        ProfileLink::factory()->createOne(['user_id' => $profile->user_id, 'label' => 'Сайт', 'url' => 'http://www.example.com', 'sort' => 1]);

        $resume = $this->build($profile, ProfileLocale::Ukrainian);

        $this->assertSame(ProfileLocale::Ukrainian, $resume->locale);
        $this->assertSame('Роман Нерез', $resume->fullName);
        $this->assertSame('Senior Full Stack Developer', $resume->headline);
        $this->assertSame('roman@example.com', $resume->email);
        $this->assertSame('+380 00 000 00 00', $resume->phone);
        $this->assertSame('Запоріжжя, Україна', $resume->location);
        $this->assertSame('Розробляю вебзастосунки.', $resume->summary);
        $this->assertCount(2, $resume->links);
        $this->assertSame('GitHub', $resume->links[0]->label);
        $this->assertSame('https://github.com/RomanNerez/', $resume->links[0]->url);
        $this->assertSame('github.com/RomanNerez', $resume->links[0]->displayUrl);
        $this->assertSame('example.com', $resume->links[1]->displayUrl);
    }

    /**
     * @return void
     */
    public function testEmptyFieldsAndSectionsAreNull(): void
    {
        $profile = $this->profile(['email' => '  ', 'headline' => null]);

        $resume = $this->build($profile, ProfileLocale::Ukrainian);

        $this->assertNull($resume->headline);
        $this->assertNull($resume->email);
        $this->assertNull($resume->phone);
        $this->assertNull($resume->location);
        $this->assertNull($resume->summary);
        $this->assertSame([], $resume->links);
        $this->assertSame([], $resume->skillRows);
        $this->assertSame([], $resume->languages);
        $this->assertSame([], $resume->experience);
        $this->assertSame([], $resume->education);
        $this->assertFalse($resume->hasSkillsSection());
    }

    /**
     * @return void
     */
    public function testEnglishResumeFallsBackToUkrainianValues(): void
    {
        $profile = $this->profile([
            'first_name' => ['uk' => 'Роман', 'en' => 'Roman'],
            'last_name' => ['uk' => 'Нерез', 'en' => ''],
            'headline' => ['uk' => 'Senior Full Stack розробник'],
            'location' => ['uk' => 'Запоріжжя', 'en' => 'Zaporizhzhia'],
        ]);

        $resume = $this->build($profile, ProfileLocale::English);

        $this->assertSame('Roman Нерез', $resume->fullName);
        $this->assertSame('Senior Full Stack розробник', $resume->headline);
        $this->assertSame('Zaporizhzhia', $resume->location);
    }

    /**
     * @return void
     */
    public function testSkillGroupsKeepOrderAndLanguagesUseResumeLevelLabels(): void
    {
        $profile = $this->profile();
        $frontend = ProfileSkillGroup::factory()->createOne(['user_id' => $profile->user_id, 'name' => 'Frontend', 'sort' => 1]);
        $backend = ProfileSkillGroup::factory()->createOne(['user_id' => $profile->user_id, 'name' => 'Backend', 'sort' => 0]);
        ProfileSkillGroup::factory()->createOne(['user_id' => $profile->user_id, 'name' => 'Порожня', 'sort' => 2]);
        $this->attachStacks($backend, ['PHP', 'Laravel']);
        $this->attachStacks($frontend, ['Vue.js']);
        ProfileLanguage::factory()->createOne([
            'user_id' => $profile->user_id,
            'language' => ['uk' => 'Українська', 'en' => 'Ukrainian'],
            'level' => LanguageLevel::NATIVE,
            'sort' => 0,
        ]);
        ProfileLanguage::factory()->createOne([
            'user_id' => $profile->user_id,
            'language' => ['uk' => 'Англійська', 'en' => 'English'],
            'level' => LanguageLevel::B2,
            'description' => ['uk' => 'вільне читання документації'],
            'sort' => 1,
        ]);

        $ukrainian = $this->build($profile, ProfileLocale::Ukrainian);
        $english = $this->build($profile, ProfileLocale::English);

        $this->assertSame(
            [['Backend', ['PHP', 'Laravel']], ['Frontend', ['Vue.js']]],
            array_map(static fn (ResumeSkillRow $row): array => [$row->name, $row->items], $ukrainian->skillRows),
        );
        $this->assertSame(
            ['Українська (Рідна)', 'Англійська (B2 Upper-Intermediate) — вільне читання документації'],
            $ukrainian->languages,
        );
        $this->assertSame(
            ['Ukrainian (Native)', 'English (B2 Upper-Intermediate) — вільне читання документації'],
            $english->languages,
        );
        $this->assertTrue($english->hasSkillsSection());
    }

    /**
     * @return void
     */
    public function testExperienceGivesCardPerProjectNewestFirstAndCardForExperienceWithoutProjects(): void
    {
        $profile = $this->profile();
        $giraffe = ProfileWorkExperience::factory()->createOne([
            'user_id' => $profile->user_id,
            'company_name' => 'GiraffeSoftware',
            'role' => 'Lead Developer',
            'started_on' => '2024-09-01',
            'finished_on' => '2026-04-30',
        ]);
        ProfileProject::factory()->withDescription()->createOne([
            'work_experience_id' => $giraffe->id,
            'name' => 'Equally',
            'started_on' => '2024-09-01',
            'finished_on' => '2026-04-30',
        ]);
        $pippo = ProfileProject::factory()->createOne([
            'work_experience_id' => $giraffe->id,
            'name' => 'PippoSAS',
            'started_on' => '2025-01-01',
            'finished_on' => '2026-03-31',
        ]);
        $this->attachStacks($pippo, ['PHP', 'Laravel', 'PostgreSQL']);
        ProfileWorkExperience::factory()->createOne([
            'user_id' => $profile->user_id,
            'company_name' => 'ТОВ Техпром',
            'role' => 'Junior Developer',
            'started_on' => '2021-01-01',
            'finished_on' => '2021-08-31',
        ]);

        $cards = $this->build($profile, ProfileLocale::English)->experience;

        $this->assertSame(
            [
                ['Lead Developer', 'GiraffeSoftware', 'PippoSAS', 'Jan 2025 – Mar 2026'],
                ['Lead Developer', 'GiraffeSoftware', 'Equally', 'Sep 2024 – Apr 2026'],
                ['Junior Developer', 'ТОВ Техпром', null, 'Jan 2021 – Aug 2021'],
            ],
            array_map(static fn (ResumeExperienceCard $card): array => [$card->role, $card->organization, $card->title, $card->period], $cards),
        );
        $this->assertSame(['PHP', 'Laravel', 'PostgreSQL'], $cards[0]->stack);
        $this->assertNull($cards[0]->descriptionHtml);
        $this->assertStringContainsString('Database migration from MySQL to PostgreSQL.', (string) $cards[1]->descriptionHtml);
        $this->assertSame([], $cards[2]->stack);
        $this->assertNull($cards[2]->descriptionHtml);
    }

    /**
     * @return void
     */
    public function testFreelanceExperienceShowsTranslatedFreelanceLabel(): void
    {
        $profile = $this->profile();
        ProfileWorkExperience::factory()->freelance()->createOne(['user_id' => $profile->user_id, 'company_name' => 'Клієнт']);

        $this->assertSame('Фріланс-проєкт', $this->build($profile, ProfileLocale::Ukrainian)->experience[0]->organization);
        $this->assertSame('Freelance project', $this->build($profile, ProfileLocale::English)->experience[0]->organization);
    }

    /**
     * @return void
     */
    public function testOpenPeriodUsesResumeLanguage(): void
    {
        $profile = $this->profile();
        $experience = ProfileWorkExperience::factory()->current()->createOne(['user_id' => $profile->user_id, 'started_on' => '2024-03-01']);
        ProfileProject::factory()->current()->createOne(['work_experience_id' => $experience->id, 'started_on' => '2024-03-01']);

        $this->assertSame('Mar 2024 – Present', $this->build($profile, ProfileLocale::English)->experience[0]->period);
        $this->assertSame('Бер 2024 – Теперішній час', $this->build($profile, ProfileLocale::Ukrainian)->experience[0]->period);
    }

    /**
     * @return void
     */
    public function testEducationShowsInstitutionAsHeadingAndCollapsesEqualYears(): void
    {
        $profile = $this->profile();
        ProfileEducation::factory()->createOne([
            'user_id' => $profile->user_id,
            'title' => 'Front-End розробка',
            'institution' => 'Brain Academy',
            'description' => 'HTML, CSS, JavaScript',
            'started_on' => '2019-02-01',
            'finished_on' => '2019-06-30',
            'sort' => 0,
        ]);
        ProfileEducation::factory()->createOne([
            'user_id' => $profile->user_id,
            'title' => 'AWS Certified Developer',
            'institution' => null,
            'started_on' => '2016-09-01',
            'finished_on' => '2020-06-30',
            'sort' => 1,
        ]);
        ProfileEducation::factory()->createOne([
            'user_id' => $profile->user_id,
            'title' => 'Самонавчання',
            'institution' => null,
            'sort' => 2,
        ]);

        $education = $this->build($profile, ProfileLocale::Ukrainian)->education;

        $this->assertSame('Brain Academy', $education[0]->heading);
        $this->assertSame('Front-End розробка', $education[0]->subtitle);
        $this->assertSame('HTML, CSS, JavaScript', $education[0]->description);
        $this->assertSame('2019', $education[0]->years);
        $this->assertSame('AWS Certified Developer', $education[1]->heading);
        $this->assertNull($education[1]->subtitle);
        $this->assertSame('2016 – 2020', $education[1]->years);
        $this->assertNull($education[2]->years);
    }

    /**
     * @return void
     */
    public function testProjectDescriptionKeepsFormattingAndDropsScripts(): void
    {
        $profile = $this->profile();
        $experience = ProfileWorkExperience::factory()->createOne(['user_id' => $profile->user_id]);
        ProfileProject::factory()->createOne([
            'work_experience_id' => $experience->id,
            'description' => '<p><strong>Задачі</strong></p><script>alert(1)</script><ul><li><p>Перший</p></li><li><p>Другий</p></li></ul>',
        ]);

        $html = (string) $this->build($profile, ProfileLocale::Ukrainian)->experience[0]->descriptionHtml;

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringContainsString('<strong>Задачі</strong>', $html);
        $this->assertSame(1, substr_count($html, '<ul>'));
        $this->assertSame(2, substr_count($html, '<li>'));
    }

    /**
     * @return void
     */
    public function testFileNameUsesEnglishNameOrTransliteratedUkrainianName(): void
    {
        $english = $this->profile(['first_name' => ['uk' => 'Роман', 'en' => 'Roman'], 'last_name' => ['uk' => 'Нерез', 'en' => 'Nerez']]);
        $ukrainianOnly = $this->profile(['first_name' => ['uk' => 'Роман'], 'last_name' => ['uk' => 'Нерез']]);

        $this->assertSame('Roman_Nerez_CV_UA.pdf', $this->build($english, ProfileLocale::Ukrainian)->fileName);
        $this->assertSame('Roman_Nerez_CV_EN.pdf', $this->build($english, ProfileLocale::English)->fileName);
        $this->assertSame('Roman_Nerez_CV_EN.pdf', $this->build($ukrainianOnly, ProfileLocale::English)->fileName);
    }

    /**
     * @return void
     */
    public function testMissingProfileThrowsResumeNotAvailable(): void
    {
        $user = User::factory()->createOne();

        $this->expectException(ResumeNotAvailable::class);

        app(BuildResumeTask::class)->run($user->id, ProfileLocale::Ukrainian);
    }

    /**
     * @return void
     */
    public function testOtherUsersDataIsNotIncluded(): void
    {
        $owner = $this->profile(['first_name' => 'Олена']);
        $other = $this->profile(['first_name' => 'Роман']);
        ProfileLink::factory()->createOne(['user_id' => $other->user_id, 'label' => 'GitHub']);
        ProfileSkillGroup::factory()->withStacks()->createOne(['user_id' => $other->user_id]);
        ProfileLanguage::factory()->createOne(['user_id' => $other->user_id]);
        ProfileEducation::factory()->createOne(['user_id' => $other->user_id]);
        ProfileWorkExperience::factory()->createOne(['user_id' => $other->user_id]);

        $resume = $this->build($owner, ProfileLocale::Ukrainian);

        $this->assertStringStartsWith('Олена ', $resume->fullName);
        $this->assertSame([], $resume->links);
        $this->assertSame([], $resume->skillRows);
        $this->assertSame([], $resume->languages);
        $this->assertSame([], $resume->education);
        $this->assertSame([], $resume->experience);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return UserProfile
     */
    private function profile(array $attributes = []): UserProfile
    {
        return UserProfile::factory()->createOne($attributes);
    }

    /**
     * @param UserProfile $profile
     * @param ProfileLocale $locale
     *
     * @return Resume
     */
    private function build(UserProfile $profile, ProfileLocale $locale): Resume
    {
        return app(BuildResumeTask::class)->run($profile->user_id, $locale);
    }

    /**
     * @param ProfileSkillGroup|ProfileProject $owner
     * @param list<string> $names
     *
     * @return void
     */
    private function attachStacks(ProfileSkillGroup|ProfileProject $owner, array $names): void
    {
        foreach ($names as $sort => $name) {
            $owner->stacks()->attach(Stack::factory()->createOne(['name' => $name])->id, ['sort' => $sort]);
        }
    }
}
