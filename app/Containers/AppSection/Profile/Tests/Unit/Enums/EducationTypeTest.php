<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Enums;

use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EducationType::class)]
final class EducationTypeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasThreeTypesWithUkrainianLabels(): void
    {
        $labels = array_map(static fn (EducationType $type): string => $type->getLabel(), EducationType::cases());

        $this->assertSame(['Університет', 'Курс', 'Сертифікат'], $labels);
    }
}
