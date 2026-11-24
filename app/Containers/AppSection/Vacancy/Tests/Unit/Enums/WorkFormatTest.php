<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Enums;

use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WorkFormat::class)]
final class WorkFormatTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasRemoteHybridAndOfficeWithUkrainianLabels(): void
    {
        $labels = [];

        foreach (WorkFormat::cases() as $format) {
            $labels[$format->value] = $format->getLabel();
        }

        $this->assertSame(['remote' => 'Віддалено', 'hybrid' => 'Гібрид', 'office' => 'Офіс'], $labels);
    }
}
