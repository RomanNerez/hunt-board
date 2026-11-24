<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Enums;

use App\Containers\AppSection\Resume\Enums\ResumeLanguageLevel;
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
}
