<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Support;

use App\Containers\AppSection\Resume\Support\ResumeLinkIcon;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResumeLinkIcon::class)]
final class ResumeLinkIconTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testEveryKeyIsRenderableBladeIcon(): void
    {
        foreach (array_keys(ResumeLinkIcon::all()) as $key) {
            $this->assertStringContainsString('<svg', svg($key)->toHtml(), $key);
        }
    }

    /**
     * @return void
     */
    public function testIncludesGithubAndLinkedin(): void
    {
        $this->assertArrayHasKey('fab-github', ResumeLinkIcon::brands());
        $this->assertArrayHasKey('fab-linkedin', ResumeLinkIcon::brands());
    }

    /**
     * @return void
     */
    public function testPreviewOptionsContainIconAndName(): void
    {
        $options = ResumeLinkIcon::groupedWithPreview();

        $this->assertStringContainsString('<svg', $options['Бренди']['fab-github']);
        $this->assertStringContainsString('GitHub', $options['Бренди']['fab-github']);
        $this->assertSame(array_keys(ResumeLinkIcon::all()), array_keys([...$options['Іконки'], ...$options['Бренди']]));
    }
}
