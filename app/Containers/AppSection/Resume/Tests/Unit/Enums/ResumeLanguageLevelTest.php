<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Enums;

use App\Containers\AppSection\Resume\Enums\ResumeLanguageLevel;
use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeLanguageLevel::class)]
final class ResumeLanguageLevelTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasSevenValuesWithLabels(): void
    {
        $labels = [];

        foreach (ResumeLanguageLevel::cases() as $level) {
            $labels[$level->value] = $level->getLabel();
        }

        $this->assertSame([
            'a1' => 'A1 Beginner',
            'a2' => 'A2 Elementary',
            'b1' => 'B1 Intermediate',
            'b2' => 'B2 Upper-Intermediate',
            'c1' => 'C1 Advanced',
            'c2' => 'C2 Proficient',
            'native' => 'Рідна',
        ], $labels);
    }

    /**
     * @return void
     */
    public function testPdfLabelsFollowResumeLocale(): void
    {
        $english = [];
        $ukrainian = [];

        foreach (ResumeLanguageLevel::cases() as $level) {
            $english[$level->value] = $level->pdfLabel(ResumeLocale::English);
            $ukrainian[$level->value] = $level->pdfLabel(ResumeLocale::Ukrainian);
        }

        $this->assertSame([
            'a1' => 'A1 Beginner',
            'a2' => 'A2 Elementary',
            'b1' => 'B1 Intermediate',
            'b2' => 'B2 Upper-Intermediate',
            'c1' => 'C1 Advanced',
            'c2' => 'C2 Proficient',
            'native' => 'Native',
        ], $english);
        $this->assertSame([
            'a1' => 'A1 Початковий',
            'a2' => 'A2 Елементарний',
            'b1' => 'B1 Середній',
            'b2' => 'B2 Вище середнього',
            'c1' => 'C1 Просунутий',
            'c2' => 'C2 Вільне володіння',
            'native' => 'Рідна',
        ], $ukrainian);
    }
}
