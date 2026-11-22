<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Enums;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileLocale::class)]
final class ProfileLocaleTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUkrainianComesFirstAndIsPrimary(): void
    {
        $this->assertSame(['uk', 'en'], ProfileLocale::values());
        $this->assertSame(ProfileLocale::Ukrainian, ProfileLocale::primary());
        $this->assertTrue(ProfileLocale::Ukrainian->isPrimary());
        $this->assertFalse(ProfileLocale::English->isPrimary());
    }

    /**
     * @return void
     */
    public function testLabelsAreLanguageSelfNames(): void
    {
        $this->assertSame(['Українська', 'English'], array_map(static fn (ProfileLocale $locale): string => $locale->getLabel(), ProfileLocale::cases()));
    }
}
