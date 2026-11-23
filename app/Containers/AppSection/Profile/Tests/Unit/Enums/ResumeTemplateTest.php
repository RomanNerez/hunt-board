<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Enums;

use App\Containers\AppSection\Profile\Enums\ResumeTemplate;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeTemplate::class)]
final class ResumeTemplateTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testClassicTemplateIsLabelledInUkrainian(): void
    {
        $this->assertSame('Класичний', ResumeTemplate::Classic->getLabel());
    }

    /**
     * @return void
     */
    public function testEveryTemplatePointsToAnExistingView(): void
    {
        foreach (ResumeTemplate::cases() as $template) {
            $this->assertSame("appSection@profile::resume.templates.{$template->value}", $template->view());
            $this->assertTrue(view()->exists($template->view()));
        }
    }
}
