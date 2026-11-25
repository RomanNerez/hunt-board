<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Enums;

use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeLocale::class)]
final class ResumeLocaleTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasEnglishAndUkrainianWithLabelsAndFileSuffixes(): void
    {
        $locales = [];

        foreach (ResumeLocale::cases() as $locale) {
            $locales[$locale->value] = [$locale->getLabel(), $locale->fileSuffix()];
        }

        $this->assertSame([
            'en' => ['English', 'EN'],
            'uk' => ['Українська', 'UA'],
        ], $locales);
    }

    /**
     * @return void
     */
    public function testDefaultIsEnglish(): void
    {
        $this->assertSame(ResumeLocale::English, ResumeLocale::default());
    }
}
