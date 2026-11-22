<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Enums;

use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageLevel::class)]
final class LanguageLevelTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testLevelsGoFromBeginnerToNative(): void
    {
        $labels = array_map(static fn (LanguageLevel $level): string => $level->getLabel(), LanguageLevel::cases());

        $this->assertSame(
            ['A1 Beginner', 'A2 Elementary', 'B1 Intermediate', 'B2 Upper-Intermediate', 'C1 Advanced', 'C2 Proficient', 'Рідна'],
            $labels,
        );
    }
}
