<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Enums\ResumeLanguageLevel;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeLanguagesTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReplaceResumeLanguagesTask::class)]
final class ReplaceResumeLanguagesTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testReplacesListWithPositionAsSortOrderAndVisibleByDefault(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Англійська']);

        app(ReplaceResumeLanguagesTask::class)->run($resume, [
            ['language' => 'Англійська', 'level' => 'b2', 'level_label' => 'Intermediate'],
            ['language' => 'Українська', 'level' => ResumeLanguageLevel::Native, 'level_label' => '', 'is_visible' => false],
            ['language' => 'Польська', 'level' => '', 'level_label' => 'Розмовна'],
        ]);

        $languages = $resume->languages()->get();

        $this->assertSame(['Англійська', 'Українська', 'Польська'], $languages->pluck('language')->all());
        $this->assertSame([0, 1, 2], $languages->pluck('sort_order')->all());
        $this->assertSame([true, false, true], $languages->pluck('is_visible')->all());
        $this->assertSame(ResumeLanguageLevel::B2, $languages[0]->level);
        $this->assertSame('Intermediate', $languages[0]->level_label);
        $this->assertSame(ResumeLanguageLevel::Native, $languages[1]->level);
        $this->assertNull($languages[1]->level_label);
        $this->assertNull($languages[2]->level);
        $this->assertSame('Розмовна', $languages[2]->level_label);
    }

    /**
     * @return void
     */
    public function testDoesNotTouchLanguagesOfOtherResumes(): void
    {
        $resume = Resume::factory()->createOne();
        $other = ResumeLanguage::factory()->createOne();

        app(ReplaceResumeLanguagesTask::class)->run($resume, []);

        $this->assertModelExists($other);
    }
}
