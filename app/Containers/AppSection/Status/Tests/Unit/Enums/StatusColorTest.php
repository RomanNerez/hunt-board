<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Enums;

use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StatusColor::class)]
final class StatusColorTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testEveryPresetHasLabelAndHexColorsForBothThemes(): void
    {
        foreach (StatusColor::cases() as $color) {
            $this->assertNotSame('', $color->getLabel());

            foreach ([$color->lightBackground(), $color->lightBorder(), $color->lightText(), $color->darkText()] as $hex) {
                $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $hex, $color->value);
            }

            foreach ([$color->darkBackground(), $color->darkBorder()] as $hex) {
                $this->assertMatchesRegularExpression('/^#[0-9A-F]{8}$/', $hex, $color->value);
            }
        }
    }

    /**
     * @return void
     */
    public function testEveryPresetHasDistinctUkrainianLabel(): void
    {
        $labels = array_map(static fn (StatusColor $color): string => $color->getLabel(), StatusColor::cases());

        $this->assertSame(
            ['Сірий', 'Червоний', 'Помаранчевий', 'Бурштиновий', 'Лаймовий', 'Зелений', 'Бірюзовий', 'Блакитний', 'Синій', 'Індиго', 'Фіолетовий', 'Рожевий'],
            $labels,
        );
    }

    /**
     * @return void
     */
    public function testBluePresetUsesTailwindShades(): void
    {
        $this->assertSame('Синій', StatusColor::BLUE->getLabel());
        $this->assertSame('#EFF6FF', StatusColor::BLUE->lightBackground());
        $this->assertSame('#BFDBFE', StatusColor::BLUE->lightBorder());
        $this->assertSame('#1D4ED8', StatusColor::BLUE->lightText());
        $this->assertSame('#3B82F626', StatusColor::BLUE->darkBackground());
        $this->assertSame('#3B82F64D', StatusColor::BLUE->darkBorder());
        $this->assertSame('#93C5FD', StatusColor::BLUE->darkText());
    }
}
