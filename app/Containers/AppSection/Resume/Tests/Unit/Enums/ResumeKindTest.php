<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Enums;

use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeKind::class)]
final class ResumeKindTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasMasterAndApplicationWithUkrainianLabels(): void
    {
        $labels = [];

        foreach (ResumeKind::cases() as $kind) {
            $labels[$kind->value] = $kind->getLabel();
        }

        $this->assertSame(['master' => 'Основне', 'application' => 'Під вакансію'], $labels);
    }

    /**
     * @return void
     */
    public function testEveryKindHasColor(): void
    {
        foreach (ResumeKind::cases() as $kind) {
            $this->assertNotSame('', $kind->getColor());
        }
    }
}
