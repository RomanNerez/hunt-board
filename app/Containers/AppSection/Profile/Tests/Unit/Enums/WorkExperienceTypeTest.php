<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Enums;

use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WorkExperienceType::class)]
final class WorkExperienceTypeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasCompanyAndFreelanceWithUkrainianLabels(): void
    {
        $labels = [];

        foreach (WorkExperienceType::cases() as $type) {
            $labels[$type->value] = $type->getLabel();
        }

        $this->assertSame(['company' => 'Компанія', 'freelance' => 'Фриланс-проєкт'], $labels);
    }
}
