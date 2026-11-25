<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Enums;

use App\Containers\AppSection\Resume\Enums\ResumeEmploymentType;
use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeEmploymentType::class)]
final class ResumeEmploymentTypeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasFiveValuesWithUkrainianLabels(): void
    {
        $labels = [];

        foreach (ResumeEmploymentType::cases() as $type) {
            $labels[$type->value] = $type->getLabel();
        }

        $this->assertSame([
            'full_time' => 'Повна зайнятість',
            'part_time' => 'Часткова зайнятість',
            'contract' => 'Контракт',
            'freelance' => 'Фріланс',
            'internship' => 'Стажування',
        ], $labels);
    }

    /**
     * @return void
     */
    public function testPdfLabelsFollowResumeLocale(): void
    {
        $english = [];
        $ukrainian = [];

        foreach (ResumeEmploymentType::cases() as $type) {
            $english[$type->value] = $type->pdfLabel(ResumeLocale::English);
            $ukrainian[$type->value] = $type->pdfLabel(ResumeLocale::Ukrainian);
        }

        $this->assertSame([
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship',
        ], $english);
        $this->assertSame([
            'full_time' => 'Повна зайнятість',
            'part_time' => 'Часткова зайнятість',
            'contract' => 'Контракт',
            'freelance' => 'Фріланс',
            'internship' => 'Стажування',
        ], $ukrainian);
    }
}
